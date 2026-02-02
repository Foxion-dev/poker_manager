<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\AddTournamentCommand;
use App\Services\Telegram\TelegramHandlerInterface;
use App\Services\Telegram\TelegramUpdate;

class AddTournamentHandler implements TelegramHandlerInterface
{
	private const CALLBACK_PREFIX_ROOM = 'add_room:';
	private const CALLBACK_PREFIX_CURRENCY = 'add_currency:';

	public function __construct(
		private AddTournamentCommand $addTournamentCommand
	) {
	}

	public function canHandle(TelegramUpdate $update): bool
	{
		if ($update->isCallback) {
			$data = $update->callbackData ?? '';
			return str_starts_with($data, self::CALLBACK_PREFIX_ROOM)
				|| str_starts_with($data, self::CALLBACK_PREFIX_CURRENCY);
		}
		$lower = mb_strtolower($update->text);
		if ($lower === '/add') {
			return true;
		}
		return AddTournamentCommand::getCachedState($update->chatId) !== null;
	}

	public function handle(TelegramUpdate $update): string|array|null
	{
		if ($update->isCallback) {
			$data = $update->callbackData ?? '';
			if (str_starts_with($data, self::CALLBACK_PREFIX_ROOM)) {
				$roomId = (int) substr($data, strlen(self::CALLBACK_PREFIX_ROOM));
				return $this->addTournamentCommand->handleRoomSelected($update->chatId, $roomId);
			}
			if (str_starts_with($data, self::CALLBACK_PREFIX_CURRENCY)) {
				$currencyId = (int) substr($data, strlen(self::CALLBACK_PREFIX_CURRENCY));
				return $this->addTournamentCommand->handleCurrencySelected($update->chatId, $currencyId);
			}
			return null;
		}
		$state = AddTournamentCommand::getCachedState($update->chatId);
		$context = $state !== null ? ['state' => $state] : [];
		return $this->addTournamentCommand->run($update->chatId, $update->text, $context);
	}
}
