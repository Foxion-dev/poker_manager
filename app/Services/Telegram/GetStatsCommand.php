<?php

namespace App\Services\Telegram;

use App\Models\UserSetting;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class GetStatsCommand implements TelegramCommandInterface
{
	private const CACHE_KEY_PREFIX = 'telegram_get_stats:';
	private const CACHE_TTL = 600;

	public function __construct(
		private StatisticsService $statisticsService
	) {
	}

	public function run(int $chatId, string $text, array $context = []): string|array|null
	{
		$state = $context['state'] ?? null;
		$lower = mb_strtolower(trim($text));

		if ($lower === '/get_stats' || $state === null) {
			Cache::put(self::CACHE_KEY_PREFIX . $chatId, ['step' => 'period'], self::CACHE_TTL);
			return [
				'text' => "<b>📊 Статистика</b>\n\nВыберите период:",
				'reply_markup' => [
					'inline_keyboard' => [
						[
							['text' => '📅 Неделя', 'callback_data' => 'stats_period:1'],
							['text' => '📅 Месяц', 'callback_data' => 'stats_period:2'],
						],
						[
							['text' => '📅 Год', 'callback_data' => 'stats_period:3'],
							['text' => '📅 Свой период', 'callback_data' => 'stats_period:4'],
						],
					],
				],
			];
		}

		$userSetting = UserSetting::where('telegram_chat_id', $chatId)->first();
		if (!$userSetting) {
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return "Сначала привяжите аккаунт. /start КОД";
		}

		$user = $userSetting->user;

		if ($state['step'] === 'period') {
			if (!in_array($lower, ['1', '2', '3', '4'], true)) {
				return "Введите цифру от 1 до 4.";
			}
			if ($lower === '4') {
				Cache::put(self::CACHE_KEY_PREFIX . $chatId, ['step' => 'date_from'], self::CACHE_TTL);
				return "Введите <b>дату начала</b> в формате ДД.ММ.ГГГГ\nНапример: 01.01.2025";
			}
			[$start, $end] = $this->periodToRange($lower);
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return $this->formatStats($user, $start, $end);
		}

		if ($state['step'] === 'date_from') {
			$dateFrom = $this->parseDate($text);
			if ($dateFrom === null) {
				return "Неверный формат. Введите дату: <b>ДД.ММ.ГГГГ</b>\nНапример: 01.01.2025";
			}
			Cache::put(self::CACHE_KEY_PREFIX . $chatId, [
				'step' => 'date_to',
				'date_from' => $dateFrom->format('Y-m-d'),
			], self::CACHE_TTL);
			return "Введите <b>дату окончания</b> в формате ДД.ММ.ГГГГ\nНапример: 31.01.2025";
		}

		if ($state['step'] === 'date_to') {
			$dateTo = $this->parseDate($text);
			if ($dateTo === null) {
				return "Неверный формат. Введите дату: <b>ДД.ММ.ГГГГ</b>\nНапример: 31.01.2025";
			}
			$dateFrom = isset($state['date_from']) ? Carbon::parse($state['date_from']) : null;
			if ($dateFrom && $dateTo->lt($dateFrom)) {
				return "Дата окончания должна быть не раньше даты начала.";
			}
			Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
			return $this->formatStats($user, $dateFrom, $dateTo);
		}

		Cache::forget(self::CACHE_KEY_PREFIX . $chatId);
		return null;
	}

	public static function getCachedState(int $chatId): ?array
	{
		return Cache::get(self::CACHE_KEY_PREFIX . $chatId);
	}

	private function periodToRange(string $choice): array
	{
		$end = Carbon::today()->endOfDay();
		$start = match ($choice) {
			'1' => Carbon::today()->subDays(6)->startOfDay(),
			'2' => Carbon::today()->subDays(29)->startOfDay(),
			'3' => Carbon::today()->subDays(364)->startOfDay(),
			default => Carbon::today()->subDays(29)->startOfDay(),
		};
		return [$start, $end];
	}

	private function parseDate(string $text): ?Carbon
	{
		$text = trim(str_replace([' ', '-'], ['.', '.'], $text));
		$date = Carbon::createFromFormat('d.m.Y', $text);
		if ($date === false) {
			return null;
		}
		return $date->startOfDay();
	}

	private function formatStats($user, ?Carbon $startDate, ?Carbon $endDate): string
	{
		$totalProfit = $this->statisticsService->getTotalProfit($user, $startDate, $endDate);
		$itmPct = $this->statisticsService->getITMPercentage($user, $startDate, $endDate);
		$avgBuyin = $this->statisticsService->getAverageBuyin($user, $startDate, $endDate);
		$roi = $this->statisticsService->getROI($user, $startDate, $endDate);
		$avgCashout = $this->statisticsService->getAverageCashout($user, $startDate, $endDate);
		$bountyProfit = $this->statisticsService->getTotalBountyProfit($user, $startDate, $endDate);

		$totalTournaments = $user->tournaments()
			->when($startDate, fn($q) => $q->where('date', '>=', $startDate))
			->when($endDate, fn($q) => $q->where('date', '<=', $endDate))
			->count();
		$totalPacks = $user->packs()
			->when($startDate, fn($q) => $q->where('start_date', '>=', $startDate))
			->when($endDate, fn($q) => $q->where('start_date', '<=', $endDate))
			->count();
		$totalEntries = $totalTournaments + $totalPacks;

		$periodStr = $startDate && $endDate
			? $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y')
			 : 'период';

		$profitStr = $this->formatMoney($totalProfit);
		$avgBuyinStr = $this->formatMoney($avgBuyin);
		$avgCashoutStr = $this->formatMoney($avgCashout);
		$bountyStr = $this->formatMoney($bountyProfit);

		$lines = [
			"<b>📊 Статистика</b>",
			$this->escapeHtml($periodStr),
			"",
			"<b>Турниры / паки</b>: {$totalEntries}",
			"<b>Прибыль</b>: {$profitStr}",
			"<b>ITM</b>: {$itmPct}%",
			"<b>ROI</b>: {$roi}%",
			"<b>Ср. бай-ин</b>: {$avgBuyinStr}",
			"<b>Ср. кэшаут</b>: {$avgCashoutStr}",
		];
		if ((float) $bountyProfit !== 0.0) {
			$lines[] = "<b>Бounty</b>: {$bountyStr}";
		}
		return implode("\n", $lines);
	}

	private function formatMoney(float $value): string
	{
		$formatted = number_format(abs($value), 2, '.', ' ');
		return $value >= 0 ? "\${$formatted}" : "-$" . $formatted;
	}

	private function escapeHtml(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
}
