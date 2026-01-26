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
		$query = $request->user()->tournaments()->with(['room', 'currency']);

		if ($request->has('room_id')) {
			$query->where('room_id', $request->room_id);
		}

		if ($request->has('date_from')) {
			$query->where('date', '>=', $request->date_from);
		}

		if ($request->has('date_to')) {
			$query->where('date', '<=', $request->date_to);
		}

		$tournaments = $query->orderBy('date', 'desc')
			->orderBy('id', 'desc')
			->paginate($request->get('per_page', 15));

		return response()->json($tournaments);
	}

	public function store(StoreTournamentRequest $request): JsonResponse
	{
		$tournament = $request->user()->tournaments()->create($request->validated());

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
