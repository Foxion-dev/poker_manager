<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\TelegramHandlerInterface;
use App\Services\Telegram\TelegramUpdate;
use App\Services\TelegramLinkService;

class StartHandler implements TelegramHandlerInterface
{
	public function __construct(
		private TelegramLinkService $linkService
	) {
	}

	public function canHandle(TelegramUpdate $update): bool
	{
		if ($update->isCallback) {
			return false;
		}
		$lower = mb_strtolower($update->text);
		return $lower === '/start' || str_starts_with($lower, '/start ');
	}

	public function handle(TelegramUpdate $update): string|array|null
	{
		$parts = preg_split('/\s+/', $update->text, 2);
		$code = isset($parts[1]) ? trim($parts[1]) : '';
		if ($code === '') {
			$existing = \App\Models\UserSetting::where('telegram_chat_id', $update->chatId)->first();
			if ($existing) {
				$name = $this->escapeHtml($existing->user->name);
				return "Вы уже привязаны к аккаунту <b>{$name}</b>. /balance — баланс по румам.";
			}
			return "Чтобы привязать аккаунт, получите код в настройках приложения и отправьте: /start КОД";
		}
		$user = $this->linkService->linkByCode($code, $update->chatId, $update->username);
		if ($user) {
			$name = $this->escapeHtml($user->name);
			return "Вы привязаны к аккаунту <b>{$name}</b>. Используйте /balance для просмотра баланса по румам.";
		}
		return "Код неверный или истёк. Получите новый код в настройках приложения.";
	}

	private function escapeHtml(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
}
