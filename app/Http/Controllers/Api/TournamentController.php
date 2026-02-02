<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTournamentRequest;
use App\Http\Requests\Api\UpdateTournamentRequest;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$query = $request->user()->tournaments()->with(['room.currency', 'currency']);

		if ($request->has('room_id')) {
			$query->where('room_id', $request->room_id);
		}

		if ($request->has('date_from')) {
			$query->where('date', '>=', $request->date_from);
		}

		if ($request->has('date_to')) {
			$query->where('date', '<=', $request->date_to . ' 23:59:59');
		}

		if ($request->has('buyin_min')) {
			$query->where('buyin', '>=', $request->buyin_min);
		}

		if ($request->has('buyin_max')) {
			$query->where('buyin', '<=', $request->buyin_max);
		}

		if ($request->has('cashout_min')) {
			$query->where('cashout', '>=', $request->cashout_min);
		}

		if ($request->has('cashout_max')) {
			$query->where('cashout', '<=', $request->cashout_max);
		}

		$sortBy = $request->get('sort_by', 'date');
		$sortOrder = $request->get('sort_order', 'desc');

		$allowedSortFields = ['date', 'buyin', 'cashout', 'place'];
		if (in_array($sortBy, $allowedSortFields)) {
			if ($sortBy === 'buyin') {
				$query->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
					->select('tournaments.*')
					->selectRaw(\App\Models\Tournament::getBuyinUsdExpression() . ' as buyin_usd')
					->orderBy('buyin_usd', $sortOrder);
			} elseif ($sortBy === 'cashout') {
				$query->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
					->select('tournaments.*')
					->selectRaw(\App\Models\Tournament::getCashoutUsdExpression() . ' as cashout_usd')
					->orderBy('cashout_usd', $sortOrder);
			} else {
				$query->orderBy('tournaments.' . $sortBy, $sortOrder);
			}
		} else {
			$query->orderBy('tournaments.date', 'desc');
		}

		$query->orderBy('tournaments.id', 'desc');

		$tournaments = $query->paginate($request->get('per_page', 15));

		return response()->json($tournaments);
	}

	public function store(StoreTournamentRequest $request): JsonResponse
	{
		$user = $request->user();
		$data = $request->validated();
		if (!isset($data['date'])) {
			$data['date'] = now()->toDateTimeString();
		}
		$tournament = $user->tournaments()->create($data);

		return response()->json($tournament->load(['room', 'currency']), 201);
	}

	public function show(Request $request, Tournament $tournament): JsonResponse
	{
		if ($tournament->user_id !== $request->user()->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		return response()->json($tournament->load(['room', 'currency']));
	}

	public function update(UpdateTournamentRequest $request, Tournament $tournament): JsonResponse
	{
		if ($tournament->user_id !== $request->user()->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$tournament->update($request->validated());

		return response()->json($tournament->load(['room', 'currency']));
	}

	public function destroy(Request $request, Tournament $tournament): JsonResponse
	{
		if ($tournament->user_id !== $request->user()->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$tournament->delete();

		return response()->json(['message' => 'Tournament deleted successfully']);
	}
}
