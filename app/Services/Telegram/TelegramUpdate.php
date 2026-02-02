<?php

namespace App\Services\Telegram;

class TelegramUpdate
{
	public function __construct(
		public readonly int $chatId,
		public readonly string $text,
		public readonly ?string $username,
		public readonly bool $isCallback,
		public readonly ?string $callbackQueryId,
		public readonly ?string $callbackData,
	) {
	}

	public static function fromArray(array $update): ?self
	{
		$callbackQuery = $update['callback_query'] ?? null;
		if ($callbackQuery) {
			$message = $callbackQuery['message'] ?? null;
			if (!$message) {
				return null;
			}
			$chatId = (int) ($message['chat']['id'] ?? 0);
			if ($chatId === 0) {
				return null;
			}
			return new self(
				chatId: $chatId,
				text: (string) ($callbackQuery['data'] ?? ''),
				username: $callbackQuery['from']['username'] ?? null,
				isCallback: true,
				callbackQueryId: $callbackQuery['id'] ?? null,
				callbackData: (string) ($callbackQuery['data'] ?? ''),
			);
		}

		$message = $update['message'] ?? null;
		if (!$message) {
			return null;
		}
		$chatId = (int) ($message['chat']['id'] ?? 0);
		if ($chatId === 0) {
			return null;
		}
		$text = trim((string) ($message['text'] ?? ''));

		return new self(
			chatId: $chatId,
			text: $text,
			username: $message['from']['username'] ?? null,
			isCallback: false,
			callbackQueryId: null,
			callbackData: null,
		);
	}
}
