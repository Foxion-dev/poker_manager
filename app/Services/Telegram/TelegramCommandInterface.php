<?php

namespace App\Services\Telegram;

interface TelegramCommandInterface
{
	public function run(int $chatId, string $text, array $context = []): string|array|null;
}
