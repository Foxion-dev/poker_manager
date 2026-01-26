<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
	public function stats(Request $request): JsonResponse
	{
		$user = $request->user();

		$totalTournaments = $user->tournaments()->count();
		$totalProfit = $user->tournaments()
			->selectRaw('SUM(COALESCE(cashout, 0) - (buyin + (bounty_count * buyin))) as profit')
			->value('profit') ?? 0;

		$itmCount = $user->tournaments()
			->whereNotNull('cashout')
			->count();

		$itmPercentage = $totalTournaments > 0
			? round(($itmCount / $totalTournaments) * 100, 2)
			: 0;

		$averageBuyin = $user->tournaments()
			->selectRaw('AVG(buyin + (bounty_count * buyin)) as avg_buyin')
			->value('avg_buyin') ?? 0;

		$tournamentsByMonth = $user->tournaments()
			->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, COUNT(*) as count')
			->groupBy('month')
			->orderBy('month', 'desc')
			->limit(12)
			->get();

		$bankrollHistory = $user->tournaments()
			->selectRaw('date, SUM(COALESCE(cashout, 0) - (buyin + (bounty_count * buyin))) OVER (ORDER BY date, id) as running_total')
			->orderBy('date')
			->orderBy('id')
			->get()
			->map(function ($item) {
				return [
					'date' => $item->date,
					'balance' => $item->running_total,
				];
			});

		return response()->json([
			'total_tournaments' => $totalTournaments,
			'total_profit' => round($totalProfit, 2),
			'itm_count' => $itmCount,
			'itm_percentage' => $itmPercentage,
			'average_buyin' => round($averageBuyin, 2),
			'tournaments_by_month' => $tournamentsByMonth,
			'bankroll_history' => $bankrollHistory,
		]);
	}
}
