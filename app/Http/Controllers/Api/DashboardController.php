<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
	public function __construct(
		private StatisticsService $statisticsService
	) {
	}

	public function stats(Request $request): JsonResponse
	{
		$user = $request->user();
		$startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
		$endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;

		$totalTournaments = $user->tournaments()
			->when($startDate, fn($q) => $q->where('date', '>=', $startDate))
			->when($endDate, fn($q) => $q->where('date', '<=', $endDate))
			->count();

		$totalTournamentsAll = $user->tournaments()->count();

		return response()->json([
			'total_tournaments' => $totalTournaments,
			'total_tournaments_all' => $totalTournamentsAll,
			'total_profit' => $this->statisticsService->getTotalProfit($user, $startDate, $endDate),
			'itm_count' => $user->tournaments()
				->when($startDate, fn($q) => $q->where('date', '>=', $startDate))
				->when($endDate, fn($q) => $q->where('date', '<=', $endDate))
				->whereNotNull('cashout')
				->count(),
			'itm_percentage' => $this->statisticsService->getITMPercentage($user, $startDate, $endDate),
			'average_buyin' => $this->statisticsService->getAverageBuyin($user, $startDate, $endDate),
			'roi' => $this->statisticsService->getROI($user, $startDate, $endDate),
			'average_cashout' => $this->statisticsService->getAverageCashout($user, $startDate, $endDate),
			'tournaments_by_month' => $this->statisticsService->getTournamentsByPeriod($user, 'month'),
			'bankroll_history' => $this->statisticsService->getBankrollHistory($user, $startDate, $endDate),
			'statistics_by_room' => $this->statisticsService->getStatisticsByRoom($user),
		]);
	}
}
