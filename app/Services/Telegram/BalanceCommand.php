<?php

namespace App\Services\Telegram;

use App\Models\UserSetting;

class BalanceCommand implements TelegramCommandInterface
{
	public function run(int $chatId, string $text, array $context = []): string|array|null
	{
		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			return "Сначала привяжите аккаунт. Получите код в настройках приложения и отправьте: /start КОД";
		}
		$user = $userSetting->user;
		$disabledIds = $userSetting->getDisabledRoomIds();
		$userRooms = $user->userRooms()
			->whereNotIn('room_id', $disabledIds)
			->with(['room.currency', 'currency'])
			->get();
		if ($userRooms->isEmpty()) {
			return "Нет подключённых румов или все скрыты в настройках.";
		}
		$lines = ["<b>Баланс по румам</b>"];
		foreach ($userRooms as $ur) {
			$symbol = $this->escapeHtml($ur->currency?->symbol ?? $ur->room?->currency?->symbol ?? '');
			$name = $this->escapeHtml($ur->room?->name ?? 'Рум #' . $ur->room_id);
			$icon = trim((string) ($ur->room?->icon ?? ''));
			$prefix = $icon !== '' ? $icon . ' ' : '';
			$balance = number_format((float) $ur->balance, 2, '.', ' ');
			$lines[] = "{$prefix}<b>{$name}</b>: {$balance} {$symbol}";
		}
		return implode("\n", $lines);
	}

	private function escapeHtml(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
}
