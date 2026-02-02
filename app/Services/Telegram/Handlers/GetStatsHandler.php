<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\GetStatsCommand;
use App\Services\Telegram\TelegramHandlerInterface;
use App\Services\Telegram\TelegramUpdate;

class GetStatsHandler implements TelegramHandlerInterface
{
	private const CALLBACK_PREFIX = 'stats_period:';

	public function __construct(
		private GetStatsCommand $getStatsCommand
	) {
	}

	public function canHandle(TelegramUpdate $update): bool
	{
		if ($update->isCallback) {
			return str_starts_with($update->callbackData ?? '', self::CALLBACK_PREFIX);
		}
		$lower = mb_strtolower($update->text);
		if ($lower === '/get_stats') {
			return true;
		}
		return GetStatsCommand::getCachedState($update->chatId) !== null;
	}

	public function handle(TelegramUpdate $update): string|array|null
	{
		if ($update->isCallback) {
			$choice = substr($update->callbackData ?? '', strlen(self::CALLBACK_PREFIX));
			$state = GetStatsCommand::getCachedState($update->chatId) ?? ['step' => 'period'];
			return $this->getStatsCommand->run($update->chatId, $choice, ['state' => $state]);
		}
		$state = GetStatsCommand::getCachedState($update->chatId);
		$context = $state !== null ? ['state' => $state] : [];
		return $this->getStatsCommand->run($update->chatId, $update->text, $context);
	}
}
