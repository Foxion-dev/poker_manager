<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticsService
{
	public function getBankrollHistory(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
	{
		$query = $user->tournaments()->withUsd()->orderBy('tournaments.date')->orderBy('tournaments.id');

		if ($startDate) {
			$query->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$query->where('tournaments.date', '<=', $endDate);
		}

		$tournaments = $query->get();
		$runningTotal = 0;
		$history = collect();

		foreach ($tournaments as $tournament) {
			$profit = (float) $tournament->profit_usd;
			$runningTotal += $profit;

			$history->push([
				'date' => $tournament->date->format('Y-m-d'),
				'balance' => round($runningTotal, 2),
				'profit' => round($profit, 2),
			]);
		}

		return $history;
	}

	public function getTournamentsByPeriod(User $user, string $period = 'month'): Collection
	{
		$format = match ($period) {
			'day' => '%Y-%m-%d',
			'month' => '%Y-%m',
			'year' => '%Y',
			default => '%Y-%m',
		};

		return $user->tournaments()
			->selectRaw("DATE_FORMAT(date, '{$format}') as period, COUNT(*) as count")
			->groupBy('period')
			->orderBy('period', 'desc')
			->get()
			->map(function ($item) {
				return [
					'period' => $item->period,
					'count' => $item->count,
				];
			});
	}

	public function getAverageBuyin(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$query = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$query->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$query->where('tournaments.date', '<=', $endDate);
		}

		$averageBuyin = $query->selectRaw('AVG(' . \App\Models\Tournament::getBuyinUsdExpression() . ') as avg_buyin')
			->value('avg_buyin');

		return round($averageBuyin ?? 0, 2);
	}

	public function getROI(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$query = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$query->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$query->where('tournaments.date', '<=', $endDate);
		}

		$stats = $query->selectRaw('
				SUM(' . \App\Models\Tournament::getCashoutUsdExpression() . ') as total_cashout,
				SUM(' . \App\Models\Tournament::getBuyinUsdExpression() . ') as total_buyin
			')
			->first();

		if (!$stats || $stats->total_buyin == 0) {
			return 0;
		}

		$roi = (($stats->total_cashout - $stats->total_buyin) / $stats->total_buyin) * 100;

		return round($roi, 2);
	}

	public function getITMPercentage(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$query = $user->tournaments();

		if ($startDate) {
			$query->where('date', '>=', $startDate);
		}

		if ($endDate) {
			$query->where('date', '<=', $endDate);
		}

		$totalTournaments = $query->count();

		if ($totalTournaments == 0) {
			return 0;
		}

		$itmCount = (clone $query)->whereNotNull('cashout')->count();

		return round(($itmCount / $totalTournaments) * 100, 2);
	}

	public function getTotalProfit(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$query = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$query->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$query->where('tournaments.date', '<=', $endDate);
		}

		$profit = $query->selectRaw('SUM(' . \App\Models\Tournament::getProfitUsdExpression() . ') as profit')
			->value('profit');

		return round($profit ?? 0, 2);
	}

	public function getAverageCashout(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$query = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->whereNotNull('tournaments.cashout');

		if ($startDate) {
			$query->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$query->where('tournaments.date', '<=', $endDate);
		}

		$averageCashout = $query->selectRaw('AVG(' . \App\Models\Tournament::getCashoutUsdExpression() . ') as avg_cashout')
			->value('avg_cashout');

		return round($averageCashout ?? 0, 2);
	}

	public function getStatisticsByRoom(User $user): Collection
	{
		return $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->selectRaw('
				tournaments.room_id,
				COUNT(*) as total_tournaments,
				SUM(' . \App\Models\Tournament::getProfitUsdExpression() . ') as profit,
				AVG(' . \App\Models\Tournament::getBuyinUsdExpression() . ') as avg_buyin,
				SUM(CASE WHEN tournaments.cashout IS NOT NULL THEN 1 ELSE 0 END) as itm_count
			')
			->with('room')
			->groupBy('tournaments.room_id')
			->get()
			->map(function ($item) {
				return [
					'room' => $item->room,
					'total_tournaments' => $item->total_tournaments,
					'profit' => round($item->profit, 2),
					'avg_buyin' => round($item->avg_buyin, 2),
					'itm_count' => $item->itm_count,
					'itm_percentage' => $item->total_tournaments > 0
						? round(($item->itm_count / $item->total_tournaments) * 100, 2)
						: 0,
				];
			});
	}
}
