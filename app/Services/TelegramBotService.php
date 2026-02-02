<?php

namespace App\Services;

use App\Models\TelegramBotSetting;
use App\Services\Telegram\TelegramDispatcher;
use App\Services\Telegram\TelegramUpdate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
	private const TELEGRAM_API = 'https://api.telegram.org/bot';

	public function __construct(
		private TelegramDispatcher $dispatcher
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
		$telegramUpdate = TelegramUpdate::fromArray($update);
		if ($telegramUpdate === null) {
			return;
		}
		$response = $this->dispatcher->dispatch($telegramUpdate);
		if ($telegramUpdate->isCallback && $telegramUpdate->callbackQueryId !== null) {
			$this->answerCallbackQuery($telegramUpdate->callbackQueryId);
		}
		if ($response !== null) {
			$this->sendCommandResponse($telegramUpdate->chatId, $response);
		}
	}

	public function sendCommandResponse(int $chatId, string|array $response): void
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
}
