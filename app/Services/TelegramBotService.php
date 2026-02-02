<?php

namespace App\Services;

use App\Models\TelegramBotSetting;
use App\Models\UserSetting;
use App\Services\Telegram\BalanceCommand;
use App\Services\Telegram\GetStatsCommand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
	private const TELEGRAM_API = 'https://api.telegram.org/bot';

	public function __construct(
		private TelegramLinkService $linkService,
		private BalanceCommand $balanceCommand,
		private GetStatsCommand $getStatsCommand
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

	public function getWebhookInfo(): ?array
	{
		$token = $this->getToken();
		if (!$token) {
			return null;
		}
		$response = Http::get(self::TELEGRAM_API . $token . '/getWebhookInfo');
		if (!$response->successful()) {
			return null;
		}
		return $response->json();
	}

	public function sendMessage(int $chatId, string $text, array $options = []): bool
	{
		$token = $this->getToken();
		if (!$token) {
			return false;
		}
		$payload = array_merge([
			'chat_id' => $chatId,
			'text' => $text,
			'parse_mode' => 'HTML',
		], $options);
		$response = Http::post(self::TELEGRAM_API . $token . '/sendMessage', $payload);
		if (!$response->successful()) {
			Log::warning('Telegram sendMessage failed', ['chat_id' => $chatId, 'response' => $response->body()]);
			return false;
		}
		return true;
	}

	public function answerCallbackQuery(string $callbackQueryId, ?string $text = null): bool
	{
		$token = $this->getToken();
		if (!$token) {
			return false;
		}
		$payload = ['callback_query_id' => $callbackQueryId];
		if ($text !== null && $text !== '') {
			$payload['text'] = $text;
		}
		$response = Http::post(self::TELEGRAM_API . $token . '/answerCallbackQuery', $payload);
		if (!$response->successful()) {
			Log::warning('Telegram answerCallbackQuery failed', ['response' => $response->body()]);
			return false;
		}
		return true;
	}

	public function handleUpdate(array $update): void
	{
		$callbackQuery = $update['callback_query'] ?? null;
		if ($callbackQuery) {
			$this->handleCallbackQuery($callbackQuery);
			return;
		}
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
			$response = $this->balanceCommand->run($chatId, $text, ['username' => $username]);
			if ($response !== null) {
				$this->sendCommandResponse($chatId, $response);
			}
			return;
		}
		if ($lower === '/get_stats') {
			$response = $this->getStatsCommand->run($chatId, $text, []);
			if ($response !== null) {
				$this->sendCommandResponse($chatId, $response);
			}
			return;
		}
		$getStatsState = GetStatsCommand::getCachedState($chatId);
		if ($getStatsState !== null) {
			$response = $this->getStatsCommand->run($chatId, $text, ['state' => $getStatsState]);
			if ($response !== null) {
				$this->sendCommandResponse($chatId, $response);
			}
			return;
		}
		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			$this->sendMessage($chatId, "Сначала привяжите аккаунт. Получите код в настройках приложения и отправьте: /start КОД");
			return;
		}
	}

	private function handleCallbackQuery(array $callbackQuery): void
	{
		$callbackId = $callbackQuery['id'] ?? '';
		$data = (string) ($callbackQuery['data'] ?? '');
		$message = $callbackQuery['message'] ?? null;
		if (!$message) {
			$this->answerCallbackQuery($callbackId);
			return;
		}
		$chatId = (int) ($message['chat']['id'] ?? 0);
		if ($chatId === 0) {
			$this->answerCallbackQuery($callbackId);
			return;
		}
		if (str_starts_with($data, 'stats_period:')) {
			$choice = substr($data, strlen('stats_period:'));
			$state = GetStatsCommand::getCachedState($chatId);
			if ($state === null) {
				$state = ['step' => 'period'];
			}
			$response = $this->getStatsCommand->run($chatId, $choice, ['state' => $state]);
			$this->answerCallbackQuery($callbackId);
			if ($response !== null) {
				$this->sendCommandResponse($chatId, $response);
			}
			return;
		}
		$this->answerCallbackQuery($callbackId);
	}

	private function sendCommandResponse(int $chatId, string|array $response): void
	{
		if (is_array($response)) {
			$text = $response['text'] ?? '';
			$options = [];
			if (isset($response['reply_markup'])) {
				$options['reply_markup'] = json_encode($response['reply_markup']);
			}
			$this->sendMessage($chatId, $text, $options);
			return;
		}
		$this->sendMessage($chatId, $response);
	}

	private function escapeHtml(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	private function handleStart(int $chatId, ?string $username, string $text): void
	{
		$parts = preg_split('/\s+/', $text, 2);
		$code = isset($parts[1]) ? trim($parts[1]) : '';
		if ($code === '') {
			$existing = UserSetting::where('telegram_chat_id', $chatId)->first();
			if ($existing) {
				$name = $this->escapeHtml($existing->user->name);
				$this->sendMessage($chatId, "Вы уже привязаны к аккаунту <b>{$name}</b>. /balance — баланс по румам.");
			} else {
				$this->sendMessage($chatId, "Чтобы привязать аккаунт, получите код в настройках приложения и отправьте: /start КОД");
			}
			return;
		}
		$user = $this->linkService->linkByCode($code, $chatId, $username);
		if ($user) {
			$name = $this->escapeHtml($user->name);
			$this->sendMessage($chatId, "Вы привязаны к аккаунту <b>{$name}</b>. Используйте /balance для просмотра баланса по румам.");
		} else {
			$this->sendMessage($chatId, "Код неверный или истёк. Получите новый код в настройках приложения.");
		}
	}

}
