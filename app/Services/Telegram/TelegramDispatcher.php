<?php

namespace App\Services\Telegram;

class TelegramDispatcher
{
	/**
	 * @param iterable<TelegramHandlerInterface> $handlers
	 */
	public function __construct(
		private iterable $handlers
	) {
	}

	public function dispatch(TelegramUpdate $update): string|array|null
	{
		foreach ($this->handlers as $handler) {
			if ($handler->canHandle($update)) {
				return $handler->handle($update);
			}
		}
		return null;
	}
}
