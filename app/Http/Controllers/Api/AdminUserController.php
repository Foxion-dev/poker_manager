<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
	public function __construct(
		private StatisticsService $statisticsService
	) {
	}

	public function index(Request $request): JsonResponse
	{
		$query = User::withCount('tournaments');

		if ($request->has('search')) {
			$search = $request->get('search');
			$query->where(function ($q) use ($search) {
				$q->where('name', 'like', "%{$search}%")
					->orWhere('email', 'like', "%{$search}%");
			});
		}

		if ($request->has('banned')) {
			$banned = filter_var($request->get('banned'), FILTER_VALIDATE_BOOLEAN);
			if ($banned) {
				$query->whereNotNull('banned_at');
			} else {
				$query->whereNull('banned_at');
			}
		}

		$users = $query->orderBy('created_at', 'desc')
			->paginate($request->get('per_page', 15));

		return response()->json($users);
	}

	public function show(User $user): JsonResponse
	{
		$user->loadCount('tournaments');
		return response()->json($user);
	}

	public function statistics(User $user, Request $request): JsonResponse
	{
		$startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
		$endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;

		$totalTournaments = $user->tournaments()
			->when($startDate, fn($q) => $q->where('date', '>=', $startDate))
			->when($endDate, fn($q) => $q->where('date', '<=', $endDate))
			->count();

		return response()->json([
			'total_tournaments' => $totalTournaments,
			'total_tournaments_all' => $user->tournaments()->count(),
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
			'bankroll_history' => $this->statisticsService->getBankrollHistory($user, $startDate, $endDate),
			'statistics_by_room' => $this->statisticsService->getStatisticsByRoom($user),
		]);
	}

	public function ban(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot ban yourself'], 422);
		}

		$user->update(['banned_at' => now()]);

		return response()->json(['message' => 'User banned successfully', 'user' => $user]);
	}

	public function unban(User $user): JsonResponse
	{
		$user->update(['banned_at' => null]);

		return response()->json(['message' => 'User unbanned successfully', 'user' => $user]);
	}

	public function makeAdmin(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot change your own admin status'], 422);
		}

		$user->update(['is_admin' => true]);

		return response()->json(['message' => 'User promoted to admin successfully', 'user' => $user]);
	}

	public function removeAdmin(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot change your own admin status'], 422);
		}

		$user->update(['is_admin' => false]);

		return response()->json(['message' => 'Admin rights removed successfully', 'user' => $user]);
	}

	public function destroy(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot delete yourself'], 422);
		}

		$user->delete();

		return response()->json(['message' => 'User deleted successfully']);
	}
}
