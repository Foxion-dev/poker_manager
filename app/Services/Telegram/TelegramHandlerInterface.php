<?php

namespace App\Services\Telegram;

interface TelegramHandlerInterface
{
	public function canHandle(TelegramUpdate $update): bool;

	public function handle(TelegramUpdate $update): string|array|null;
}
