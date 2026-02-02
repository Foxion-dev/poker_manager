<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\BalanceCommand;
use App\Services\Telegram\TelegramHandlerInterface;
use App\Services\Telegram\TelegramUpdate;

class BalanceHandler implements TelegramHandlerInterface
{
	public function __construct(
		private BalanceCommand $balanceCommand
	) {
	}

	public function canHandle(TelegramUpdate $update): bool
	{
		if ($update->isCallback) {
			return false;
		}
		return mb_strtolower($update->text) === '/balance';
	}

	public function handle(TelegramUpdate $update): string|array|null
	{
		return $this->balanceCommand->run($update->chatId, $update->text, [
			'username' => $update->username,
		]);
	}
}
