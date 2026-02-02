<?php

namespace App\Services;

use App\Models\TelegramBotSetting;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
	private const TELEGRAM_API = 'https://api.telegram.org/bot';

	public function __construct(
		private TelegramLinkService $linkService
	) {
	}

	public function getToken(): ?string
	{
		$settings = TelegramBotSetting::instance();
		if (!$settings->is_enabled || empty($settings->bot_token)) {
			return null;
		}
		return $settings->bot_token;
	}

	public function setWebhook(string $url): bool
	{
		$token = $this->getToken();
		if (!$token) {
			return false;
		}
		$response = Http::post(self::TELEGRAM_API . $token . '/setWebhook', [
			'url' => $url,
		]);
		if (!$response->successful()) {
			Log::warning('Telegram setWebhook failed', ['response' => $response->body()]);
			return false;
		}
		return true;
	}

	public function sendMessage(int $chatId, string $text): bool
	{
		$token = $this->getToken();
		if (!$token) {
			return false;
		}
		$response = Http::post(self::TELEGRAM_API . $token . '/sendMessage', [
			'chat_id' => $chatId,
			'text' => $text,
			'parse_mode' => null,
		]);
		if (!$response->successful()) {
			Log::warning('Telegram sendMessage failed', ['chat_id' => $chatId, 'response' => $response->body()]);
			return false;
		}
		return true;
	}

	public function handleUpdate(array $update): void
	{
		$message = $update['message'] ?? null;
		if (!$message) {
			return;
		}
		$chatId = (int) ($message['chat']['id'] ?? 0);
		$username = $message['from']['username'] ?? null;
		$text = trim((string) ($message['text'] ?? ''));
		if ($chatId === 0) {
			return;
		}
		$lower = mb_strtolower($text);
		if ($lower === '/start' || str_starts_with($lower, '/start ')) {
			$this->handleStart($chatId, $username, $text);
			return;
		}
		if ($lower === '/balance') {
			$this->handleBalance($chatId);
			return;
		}
		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			$this->sendMessage($chatId, "Сначала привяжите аккаунт. Получите код в настройках приложения и отправьте: /start КОД");
			return;
		}
	}

	private function handleStart(int $chatId, ?string $username, string $text): void
	{
		$parts = preg_split('/\s+/', $text, 2);
		$code = isset($parts[1]) ? trim($parts[1]) : '';
		if ($code === '') {
			$existing = UserSetting::where('telegram_chat_id', $chatId)->first();
			if ($existing) {
				$this->sendMessage($chatId, "Вы уже привязаны к аккаунту {$existing->user->name}. /balance — баланс по румам.");
			} else {
				$this->sendMessage($chatId, "Чтобы привязать аккаунт, получите код в настройках приложения и отправьте: /start КОД");
			}
			return;
		}
		$user = $this->linkService->linkByCode($code, $chatId, $username);
		if ($user) {
			$this->sendMessage($chatId, "Вы привязаны к аккаунту {$user->name}. Используйте /balance для просмотра баланса по румам.");
		} else {
			$this->sendMessage($chatId, "Код неверный или истёк. Получите новый код в настройках приложения.");
		}
	}

	private function handleBalance(int $chatId): void
	{
		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			$this->sendMessage($chatId, "Сначала привяжите аккаунт. Получите код в настройках приложения и отправьте: /start КОД");
			return;
		}
		$user = $userSetting->user;
		$disabledIds = $userSetting->getDisabledRoomIds();
		$userRooms = $user->userRooms()
			->whereNotIn('room_id', $disabledIds)
			->with(['room.currency', 'currency'])
			->get();
		if ($userRooms->isEmpty()) {
			$this->sendMessage($chatId, "Нет подключённых румов или все скрыты в настройках.");
			return;
		}
		$lines = ["Баланс по румам:"];
		foreach ($userRooms as $ur) {
			$symbol = $ur->currency?->symbol ?? $ur->room?->currency?->symbol ?? '';
			$name = $ur->room?->name ?? 'Рум #' . $ur->room_id;
			$lines[] = "{$name}: " . number_format((float) $ur->balance, 2, '.', ' ') . " {$symbol}";
		}
		$this->sendMessage($chatId, implode("\n", $lines));
	}
}
