<?php

namespace App\Services\Telegram;

use App\Models\Room;
use App\Models\Tournament;
use App\Models\UserSetting;
use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class AddTournamentCommand implements TelegramCommandInterface
{
	private const CACHE_KEY_PREFIX = 'telegram_add_tournament:';
	private const CACHE_TTL = 600;

	public function run(int $chatId, string $text, array $context = []): string|array|null
	{
		$state = $context['state'] ?? null;
		$text = trim($text);

		if ($text === '/add' || $state === null) {
			return $this->askRoom($chatId);
		}

		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return "Сначала привяжите аккаунт. /start КОД";
		}

		$user = $userSetting->user;

		if ($state['step'] === 'room') {
			return "Выберите рум кнопкой выше.";
		}

		if ($state['step'] === 'currency') {
			return "Выберите валюту кнопкой выше.";
		}

		if ($state['step'] === 'buyin') {
			$buyin = $this->parseDecimal($text);
			if ($buyin === null || $buyin < 0) {
				return "Введите число байина (например 10 или 10.5).";
			}
			$state['buyin'] = (string) $buyin;
			$state['step'] = 'cashout';
			Cache::put(self::CACHE_KEY_PREFIX . $chatId, $state, self::CACHE_TTL);
			return "Введите <b>кэшаут</b> (число) или отправьте <b>пусто</b>/<b>-</b>, если не вышли в плюс.";
		}

		if ($state['step'] === 'cashout') {
			$cashout = $this->parseOptionalDecimal($text);
			$state['cashout'] = $cashout;
			$state['step'] = 'cashout_bounty';
			Cache::put(self::CACHE_KEY_PREFIX . $chatId, $state, self::CACHE_TTL);
			return "Введите <b>кэшаут баунти</b> (число) или отправьте <b>пусто</b>/<b>-</b>, если нет.";
		}

		if ($state['step'] === 'cashout_bounty') {
			$cashoutBounty = $this->parseOptionalDecimal($text);
			$state['cashout_bounty'] = $cashoutBounty;
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return $this->createTournament($user, $state);
		}

		Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
		return null;
	}

	public static function getCachedState(int $chatId): ?array
	{
		return Cache::get(self::CACHE_KEY_PREFIX . $chatId);
	}

	public function handleRoomSelected(int $chatId, int $roomId): string|array|null
	{
		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return "Сначала привяжите аккаунт. /start КОД";
		}

		$user = $userSetting->user;
		$room = Room::with('currencies')->find($roomId);
		if (!$room || !$this->isRoomAvailableForUser($room, $user)) {
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return "Рум недоступен.";
		}

		$currencies = $this->getCurrenciesForRoom($room);
		if ($currencies->isEmpty()) {
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return "У рума нет валют. Добавьте валюты в настройках.";
		}

		$state = [
			'step' => 'currency',
			'room_id' => $roomId,
		];
		Cache::put(self::CACHE_KEY_PREFIX . $chatId, $state, self::CACHE_TTL);

		$rows = [];
		$row = [];
		foreach ($currencies as $c) {
			$label = $c->symbol . ' ' . $c->code;
			$row[] = ['text' => $label, 'callback_data' => 'add_currency:' . $c->id];
			if (count($row) >= 2) {
				$rows[] = $row;
				$row = [];
			}
		}
		if ($row !== []) {
			$rows[] = $row;
		}

		return [
			'text' => "<b>➕ Добавить турнир</b>\n\nРум: " . $this->escapeHtml($room->name) . "\n\nВыберите валюту:",
			'reply_markup' => ['inline_keyboard' => $rows],
		];
	}

	public function handleCurrencySelected(int $chatId, int $currencyId): string|array|null
	{
		$state = self::getCachedState($chatId);
		if ($state === null || ($state['step'] ?? '') !== 'currency' || (int) ($state['room_id'] ?? 0) === 0) {
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return "Начните заново: /add";
		}

		$room = Room::with('currencies')->find($state['room_id']);
		if (!$room) {
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return "Рум не найден. /add";
		}

		$currencies = $this->getCurrenciesForRoom($room);
		$currency = $currencies->firstWhere('id', $currencyId);
		if (!$currency) {
			return "Выберите валюту из списка кнопкой выше.";
		}

		$state['step'] = 'buyin';
		$state['currency_id'] = $currencyId;
		Cache::put(self::CACHE_KEY_PREFIX . $chatId, $state, self::CACHE_TTL);

		$symbol = $currency->symbol ?? $currency->code;
		return "Введите <b>байин</b> в выбранной валюте ({$this->escapeHtml($symbol)}):\nНапример: 10 или 10.5";
	}

	private function askRoom(int $chatId): string|array|null
	{
		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			return "Сначала привяжите аккаунт. /start КОД";
		}

		$user = $userSetting->user;
		$rooms = $this->getRoomsForUser($user);
		if ($rooms->isEmpty()) {
			return "Нет доступных румов. Добавьте румы в приложении.";
		}

		Cache::put(self::CACHE_KEY_PREFIX . $chatId, ['step' => 'room'], self::CACHE_TTL);

		$rows = [];
		$row = [];
		foreach ($rooms as $room) {
			$label = ($room->icon ?? '🎰') . ' ' . $room->name;
			if (mb_strlen($label) > 30) {
				$label = mb_substr($label, 0, 27) . '…';
			}
			$row[] = ['text' => $label, 'callback_data' => 'add_room:' . $room->id];
			if (count($row) >= 2) {
				$rows[] = $row;
				$row = [];
			}
		}
		if ($row !== []) {
			$rows[] = $row;
		}

		return [
			'text' => "<b>➕ Добавить турнир</b>\n\nВыберите рум:",
			'reply_markup' => ['inline_keyboard' => $rows],
		];
	}

	private function getRoomsForUser($user): \Illuminate\Database\Eloquent\Collection
	{
		$disabledIds = $user->getDisabledRoomIds();
		return Room::with(['currency', 'currencies'])
			->where(function ($q) use ($user) {
				$q->whereNull('user_id')->orWhere('user_id', $user->id);
			})
			->when(!empty($disabledIds), fn ($q) => $q->whereNotIn('id', $disabledIds))
			->orderByRaw('user_id IS NOT NULL')
			->orderBy('name')
			->get()
			->unique(fn (Room $r) => $r->user_id ? $r->id : $r->name)
			->values();
	}

	private function isRoomAvailableForUser(Room $room, $user): bool
	{
		if (in_array($room->id, $user->getDisabledRoomIds(), true)) {
			return false;
		}
		return $room->user_id === null || $room->user_id === $user->id;
	}

	private function getCurrenciesForRoom(Room $room): \Illuminate\Database\Eloquent\Collection
	{
		$currencies = $room->currencies;
		if ($currencies->isNotEmpty()) {
			return $currencies->sortBy('code')->values();
		}
		if ($room->currency_id) {
			$default = Currency::find($room->currency_id);
			return $default ? collect([$default]) : Currency::orderBy('code')->get();
		}
		return Currency::orderBy('code')->get();
	}

	private function parseDecimal(string $text): ?float
	{
		$text = str_replace(',', '.', trim($text));
		if ($text === '') {
			return null;
		}
		if (!preg_match('/^-?\d+(?:\.\d+)?$/', $text)) {
			return null;
		}
		$v = (float) $text;
		return $v;
	}

	private function parseOptionalDecimal(string $text): ?float
	{
		$lower = mb_strtolower(trim($text));
		if ($lower === '' || $lower === '-' || $lower === 'пусто' || $lower === 'нет' || $lower === 'пропустить') {
			return null;
		}
		return $this->parseDecimal($text);
	}

	private function createTournament($user, array $state): string
	{
		$roomId = (int) $state['room_id'];
		$currencyId = isset($state['currency_id']) ? (int) $state['currency_id'] : null;
		$buyin = (float) $state['buyin'];
		$cashout = array_key_exists('cashout', $state) ? $state['cashout'] : null;
		$cashoutBounty = array_key_exists('cashout_bounty', $state) ? $state['cashout_bounty'] : null;

		$room = Room::find($roomId);
		if (!$room || !$this->isRoomAvailableForUser($room, $user)) {
			return "Рум недоступен. /add";
		}

		if ($buyin < 0) {
			return "Байин не может быть отрицательным. /add";
		}

		Tournament::create([
			'user_id' => $user->id,
			'room_id' => $roomId,
			'currency_id' => $currencyId ?: null,
			'buyin' => $buyin,
			'date' => now()->toDateTimeString(),
			'cashout' => $cashout,
			'cashout_bounty' => $cashoutBounty,
		]);

		$roomName = $this->escapeHtml($room->name);
		return "✅ Турнир добавлен: {$roomName}, байин {$buyin}.";
	}

	private function escapeHtml(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
}
