<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\TelegramHandlerInterface;
use App\Services\Telegram\TelegramUpdate;

class DefaultHandler implements TelegramHandlerInterface
{
	public function canHandle(TelegramUpdate $update): bool
	{
		return !$update->isCallback && $update->text !== '';
	}

	public function handle(TelegramUpdate $update): string|array|null
	{
		return "Сначала привяжите аккаунт. Получите код в настройках приложения и отправьте: /start КОД";
	}
}
