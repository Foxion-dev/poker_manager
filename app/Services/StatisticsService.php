<?php

namespace App\Services;

use App\Models\User;
use App\Services\MoneyService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticsService
{
	public function getBankrollHistory(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
	{
		$tournamentsQuery = $user->tournaments()->withUsd();

		if ($startDate) {
			$tournamentsQuery->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$tournamentsQuery->where('tournaments.date', '<=', $endDate);
		}

		$tournaments = $tournamentsQuery->get();

		$packsQuery = $user->packs()->leftJoin('currencies', 'packs.currency_id', '=', 'currencies.id')
			->select('packs.*')
			->selectRaw(\App\Models\Pack::getProfitUsdExpression() . ' as profit_usd')
			->selectRaw('packs.start_date as date');

		if ($startDate) {
			$packsQuery->where('packs.start_date', '>=', $startDate);
		}

		if ($endDate) {
			$packsQuery->where('packs.start_date', '<=', $endDate);
		}

		$packs = $packsQuery->get();

		$allItems = collect();
		foreach ($tournaments as $tournament) {
			$allItems->push([
				'date' => $tournament->date,
				'profit_usd' => (float) $tournament->profit_usd,
				'id' => $tournament->id,
			]);
		}

		foreach ($packs as $pack) {
			$allItems->push([
				'date' => $pack->start_date,
				'profit_usd' => (float) $pack->profit_usd,
				'id' => 'pack_' . $pack->id,
			]);
		}

		$allItems = $allItems->sortBy(function ($item) {
			return $item['date']->format('Y-m-d') . '_' . $item['id'];
		});

		$runningTotal = 0;
		$history = collect();

		foreach ($allItems as $item) {
			$profit = $item['profit_usd'];
			$runningTotal += $profit;

			$history->push([
				'date' => $item['date']->format('Y-m-d'),
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

		$tournaments = $user->tournaments()
			->selectRaw("DATE_FORMAT(date, '{$format}') as period, COUNT(*) as count")
			->groupBy('period')
			->get();

		$packs = $user->packs()
			->selectRaw("DATE_FORMAT(start_date, '{$format}') as period, COUNT(*) as count")
			->groupBy('period')
			->get();

		$combined = collect();
		foreach ($tournaments as $item) {
			$existing = $combined->firstWhere('period', $item->period);
			if ($existing) {
				$existing->count += $item->count;
			} else {
				$combined->push($item);
			}
		}

		foreach ($packs as $item) {
			$existing = $combined->firstWhere('period', $item->period);
			if ($existing) {
				$existing->count += $item->count;
			} else {
				$combined->push($item);
			}
		}

		return $combined->sortByDesc('period')->values()->map(function ($item) {
			return [
				'period' => $item->period,
				'count' => $item->count,
			];
		});
	}

	public function getAverageBuyin(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$tournamentsQuery = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$tournamentsQuery->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$tournamentsQuery->where('tournaments.date', '<=', $endDate);
		}

		$tournamentsStats = $tournamentsQuery->selectRaw('
				SUM(' . \App\Models\Tournament::getBuyinUsdExpression() . ') as total_buyin,
				COUNT(*) as count
			')
			->first();

		$packsQuery = $user->packs()
			->leftJoin('currencies', 'packs.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$packsQuery->where('packs.start_date', '>=', $startDate);
		}

		if ($endDate) {
			$packsQuery->where('packs.start_date', '<=', $endDate);
		}

		$packsStats = $packsQuery->selectRaw('
				SUM(' . \App\Models\Pack::getBuyinUsdExpression() . ') as total_buyin,
				COUNT(*) as count
			')
			->first();

		$totalBuyin = ($tournamentsStats->total_buyin ?? 0) + ($packsStats->total_buyin ?? 0);
		$totalCount = ($tournamentsStats->count ?? 0) + ($packsStats->count ?? 0);

		if ($totalCount == 0) {
			return 0;
		}

		return round($totalBuyin / $totalCount, 2);
	}

	public function getROI(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$tournamentsQuery = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$tournamentsQuery->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$tournamentsQuery->where('tournaments.date', '<=', $endDate);
		}

		$tournamentsStats = $tournamentsQuery->selectRaw('
				SUM(' . \App\Models\Tournament::getCashoutUsdExpression() . ') as total_cashout,
				SUM(' . \App\Models\Tournament::getBuyinUsdExpression() . ') as total_buyin
			')
			->first();

		$packsQuery = $user->packs()
			->leftJoin('currencies', 'packs.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$packsQuery->where('packs.start_date', '>=', $startDate);
		}

		if ($endDate) {
			$packsQuery->where('packs.start_date', '<=', $endDate);
		}

		$packsStats = $packsQuery->selectRaw('
				SUM(' . \App\Models\Pack::getCashoutUsdExpression() . ') as total_cashout,
				SUM(' . \App\Models\Pack::getBuyinUsdExpression() . ') as total_buyin
			')
			->first();

		$totalBuyin = ($tournamentsStats->total_buyin ?? 0) + ($packsStats->total_buyin ?? 0);
		$totalCashout = ($tournamentsStats->total_cashout ?? 0) + ($packsStats->total_cashout ?? 0);

		if ($totalBuyin == 0) {
			return 0;
		}

		$roi = (($totalCashout - $totalBuyin) / $totalBuyin) * 100;

		return round($roi, 2);
	}

	public function getITMPercentage(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$tournamentsQuery = $user->tournaments();

		if ($startDate) {
			$tournamentsQuery->where('date', '>=', $startDate);
		}

		if ($endDate) {
			$tournamentsQuery->where('date', '<=', $endDate);
		}

		$totalTournaments = $tournamentsQuery->count();
		$itmTournaments = (clone $tournamentsQuery)->whereNotNull('cashout')->count();

		$packsQuery = $user->packs();

		if ($startDate) {
			$packsQuery->where('start_date', '>=', $startDate);
		}

		if ($endDate) {
			$packsQuery->where('start_date', '<=', $endDate);
		}

		$totalPacks = $packsQuery->count();
		$itmPacks = (clone $packsQuery)->whereNotNull('cashout')->count();

		$total = $totalTournaments + $totalPacks;

		if ($total == 0) {
			return 0;
		}

		$itmCount = $itmTournaments + $itmPacks;

		return round(($itmCount / $total) * 100, 2);
	}

	public function getTotalProfit(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$tournamentsQuery = $user->tournaments()->withUsd();

		if ($startDate) {
			$tournamentsQuery->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$tournamentsQuery->where('tournaments.date', '<=', $endDate);
		}

		$tournaments = $tournamentsQuery->get();
		$tournamentsProfit = $tournaments->sum(function ($tournament) {
			return (float)($tournament->profit_usd ?? 0);
		});

		$packsQuery = $user->packs()
			->leftJoin('currencies', 'packs.currency_id', '=', 'currencies.id')
			->select('packs.*')
			->selectRaw(\App\Models\Pack::getProfitUsdExpression() . ' as profit_usd');

		if ($startDate) {
			$packsQuery->where('packs.start_date', '>=', $startDate);
		}

		if ($endDate) {
			$packsQuery->where('packs.start_date', '<=', $endDate);
		}

		$packs = $packsQuery->get();
		$packsProfit = $packs->sum(function ($pack) {
			return (float)($pack->profit_usd ?? 0);
		});

		return round($tournamentsProfit + $packsProfit, 2);
	}

	public function getAverageCashout(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$tournamentsQuery = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->where(function ($query) {
				$query->whereNotNull('tournaments.cashout')
					->orWhereNotNull('tournaments.cashout_bounty');
			});

		if ($startDate) {
			$tournamentsQuery->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$tournamentsQuery->where('tournaments.date', '<=', $endDate);
		}

		$tournamentsStats = $tournamentsQuery->selectRaw('
				SUM(' . \App\Models\Tournament::getCashoutUsdExpression() . ') as total_cashout,
				COUNT(*) as count
			')
			->first();

		$packsQuery = $user->packs()
			->leftJoin('currencies', 'packs.currency_id', '=', 'currencies.id')
			->whereNotNull('packs.cashout');

		if ($startDate) {
			$packsQuery->where('packs.start_date', '>=', $startDate);
		}

		if ($endDate) {
			$packsQuery->where('packs.start_date', '<=', $endDate);
		}

		$packsStats = $packsQuery->selectRaw('
				SUM(' . \App\Models\Pack::getCashoutUsdExpression() . ') as total_cashout,
				COUNT(*) as count
			')
			->first();

		$totalCashout = ($tournamentsStats->total_cashout ?? 0) + ($packsStats->total_cashout ?? 0);
		$totalCount = ($tournamentsStats->count ?? 0) + ($packsStats->count ?? 0);

		if ($totalCount == 0) {
			return 0;
		}

		return round($totalCashout / $totalCount, 2);
	}

	public function getTotalBountyProfit(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): float
	{
		$tournamentsQuery = $user->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id');

		if ($startDate) {
			$tournamentsQuery->where('tournaments.date', '>=', $startDate);
		}

		if ($endDate) {
			$tournamentsQuery->where('tournaments.date', '<=', $endDate);
		}

		$bountyCashoutUsd = MoneyService::toUsdSqlExpression('COALESCE(tournaments.cashout_bounty, 0)', 'currencies.rate_to_usd');
		$stats = $tournamentsQuery->selectRaw("SUM({$bountyCashoutUsd}) as total_bounty_cashout")
			->first();

		$totalBountyCashout = $stats->total_bounty_cashout ?? 0;

		return round($totalBountyCashout, 2);
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
