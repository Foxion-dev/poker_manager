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
					->orWhere('user_id', $user->id)
					->orWhereHas('admins', function ($adminQuery) use ($user) {
						$adminQuery->where('user_id', $user->id);
					});
			});
		}

		$locations = $query->with('user')
			->withCount('tournaments')
			->orderBy('created_at', 'desc')
			->get()
			->map(function ($location) use ($user) {
				return [
					'id' => $location->id,
					'name' => $location->name,
					'description' => $location->description,
					'is_public' => $location->is_public,
					'has_password' => !empty($location->password),
					'user' => $location->user,
					'is_admin' => $location->isAdmin($user),
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

		if (!$location->is_public && $location->user_id !== $user->id && !$location->isAdmin($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		if ($location->is_public && $location->password) {
			$password = $request->get('password');
			if (!$password || !$location->checkPassword($password)) {
				if ($location->user_id !== $user->id && !$location->isAdmin($user)) {
					return response()->json(['message' => 'Password required', 'requires_password' => true], 403);
				}
			}
		}

		$location->load(['user', 'admins']);
		$location->loadCount('tournaments');

		return response()->json([
			'id' => $location->id,
			'name' => $location->name,
			'description' => $location->description,
			'is_public' => $location->is_public,
			'has_password' => !empty($location->password),
			'user' => $location->user,
			'is_admin' => $location->isAdmin($user),
			'can_manage_admins' => $location->canManageAdmins($user),
			'admins' => $location->admins,
			'tournaments_count' => $location->tournaments_count,
			'average_buyin' => $location->average_buyin,
			'top_players_by_wins' => $location->top_players_by_wins,
			'top_players_by_prize' => $location->top_players_by_prize,
		]);
	}

	public function update(UpdateLocationRequest $request, Location $location): JsonResponse
	{
		$user = $request->user();

		if ($location->user_id !== $user->id && !$location->canManageAdmins($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$data = $request->validated();
		if (isset($data['password']) && empty($data['password'])) {
			unset($data['password']);
		}

		$location->update($data);
		$location->load(['user', 'admins']);

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

	public function addAdmin(Location $location, Request $request): JsonResponse
	{
		$user = $request->user();

		if (!$location->canManageAdmins($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$adminUserId = $request->input('user_id');
		if (!$adminUserId || $adminUserId == $location->user_id) {
			return response()->json(['message' => 'Invalid user'], 422);
		}

		if ($location->admins()->where('user_id', $adminUserId)->exists()) {
			return response()->json(['message' => 'User is already an admin'], 422);
		}

		$location->admins()->attach($adminUserId);

		return response()->json(['message' => 'Admin added successfully']);
	}

	public function removeAdmin(Location $location, Request $request, int $adminId): JsonResponse
	{
		$user = $request->user();

		if (!$location->canManageAdmins($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		if ($adminId == $location->user_id) {
			return response()->json(['message' => 'Cannot remove location creator'], 422);
		}

		$location->admins()->detach($adminId);

		return response()->json(['message' => 'Admin removed successfully']);
	}

	public function publicShow(Location $location, Request $request): JsonResponse
	{
		if (!$location->is_public) {
			return response()->json(['message' => 'Location is not public'], 404);
		}

		if ($location->password) {
			$password = $request->get('password');
			if (!$password || !$location->checkPassword($password)) {
				return response()->json(['message' => 'Password required', 'requires_password' => true], 403);
			}
		}

		$location->load('user');
		$location->loadCount('tournaments');

		return response()->json([
			'id' => $location->id,
			'name' => $location->name,
			'description' => $location->description,
			'is_public' => $location->is_public,
			'has_password' => !empty($location->password),
			'user' => [
				'id' => $location->user->id,
				'name' => $location->user->name,
			],
			'tournaments_count' => $location->tournaments_count,
			'average_buyin' => $location->average_buyin,
			'top_players_by_wins' => $location->top_players_by_wins,
			'top_players_by_prize' => $location->top_players_by_prize,
		]);
	}
}
