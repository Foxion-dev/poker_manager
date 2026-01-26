<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLocationRequest;
use App\Http\Requests\Api\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$user = $request->user();
		$query = Location::query();

		if ($request->has('public_only')) {
			$query->where('is_public', true);
		} else {
			$query->where(function ($q) use ($user) {
				$q->where('is_public', true)
					->orWhere('user_id', $user->id);
			});
		}

		$locations = $query->with('user')
			->withCount('tournaments')
			->orderBy('created_at', 'desc')
			->get()
			->map(function ($location) {
				return [
					'id' => $location->id,
					'name' => $location->name,
					'description' => $location->description,
					'is_public' => $location->is_public,
					'user' => $location->user,
					'tournaments_count' => $location->tournaments_count,
					'average_buyin' => $location->average_buyin,
				];
			});

		return response()->json($locations);
	}

	public function store(StoreLocationRequest $request): JsonResponse
	{
		$user = $request->user();
		$location = $user->locations()->create($request->validated());

		return response()->json($location->load('user'), 201);
	}

	public function show(Location $location, Request $request): JsonResponse
	{
		$user = $request->user();

		if (!$location->is_public && $location->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$location->load('user');
		$location->loadCount('tournaments');

		return response()->json([
			'id' => $location->id,
			'name' => $location->name,
			'description' => $location->description,
			'is_public' => $location->is_public,
			'user' => $location->user,
			'tournaments_count' => $location->tournaments_count,
			'average_buyin' => $location->average_buyin,
			'top_players_by_wins' => $location->top_players_by_wins,
			'top_players_by_prize' => $location->top_players_by_prize,
		]);
	}

	public function update(UpdateLocationRequest $request, Location $location): JsonResponse
	{
		$user = $request->user();

		if ($location->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$location->update($request->validated());
		$location->load('user');

		return response()->json($location);
	}

	public function destroy(Location $location, Request $request): JsonResponse
	{
		$user = $request->user();

		if ($location->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$location->delete();

		return response()->json(['message' => 'Location deleted successfully']);
	}
}
