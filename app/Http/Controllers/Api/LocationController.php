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
					'user_id' => $location->user_id,
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

		$location->load(['user', 'admins', 'users', 'locationUsers', 'currencies']);
		$location->loadCount('tournaments');

		$usersFromSystem = $location->users->map(function ($user) use ($location) {
			$locationUser = $location->locationUsers()->where('user_id', $user->id)->first();
			$tournamentsCount = 0;
			if ($locationUser) {
				$tournamentsCount = $locationUser->tournaments_count;
			} else {
				$tournamentsCount = \App\Models\LocationTournamentParticipant::whereHas('tournament', function($query) use ($location) {
					$query->where('location_id', $location->id);
				})
				->where('user_id', $user->id)
				->count();
			}
			
			return [
				'id' => $user->pivot->id ?? $user->id,
				'name' => $user->pivot->name ?? $user->name,
				'user_id' => $user->id,
				'display_name' => $user->pivot->name ?? $user->name,
				'tournaments_count' => $tournamentsCount,
			];
		});

		$usersByName = $location->locationUsers->whereNull('user_id')->map(function ($locationUser) {
			return [
				'id' => $locationUser->id,
				'name' => $locationUser->name,
				'user_id' => null,
				'display_name' => $locationUser->name,
				'tournaments_count' => $locationUser->tournaments_count,
			];
		});

		$allLocationUsers = $usersFromSystem->concat($usersByName)->sortByDesc('tournaments_count')->values();

		return response()->json([
			'id' => $location->id,
			'name' => $location->name,
			'description' => $location->description,
			'is_public' => $location->is_public,
			'has_password' => !empty($location->password),
			'user_id' => $location->user_id,
			'user' => $location->user,
			'is_admin' => $location->isAdmin($user),
			'can_manage_admins' => $location->canManageAdmins($user),
			'admins' => $location->admins,
			'users' => $allLocationUsers->values(),
			'currencies' => $location->currencies,
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

	public function addUser(Location $location, Request $request): JsonResponse
	{
		$user = $request->user();

		if (!$location->canManageAdmins($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$userId = $request->input('user_id');
		$name = $request->input('name');

		if (!$userId && !$name) {
			return response()->json(['message' => 'Either user_id or name is required'], 422);
		}

		if ($userId) {
			if ($location->users()->where('user_id', $userId)->exists()) {
				return response()->json(['message' => 'User is already added'], 422);
			}
			$location->users()->attach($userId);
		} else {
			if ($location->locationUsers()->where('name', $name)->whereNull('user_id')->exists()) {
				return response()->json(['message' => 'User with this name is already added'], 422);
			}
			$location->locationUsers()->create(['name' => $name]);
		}

		return response()->json(['message' => 'User added successfully']);
	}

	public function removeUser(Location $location, Request $request, int $userId): JsonResponse
	{
		$user = $request->user();

		if (!$location->canManageAdmins($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$locationUser = $location->locationUsers()->find($userId);
		if ($locationUser) {
			if ($locationUser->user_id) {
				$location->users()->detach($locationUser->user_id);
			} else {
				$locationUser->delete();
			}
		} else {
			$location->users()->detach($userId);
		}

		return response()->json(['message' => 'User removed successfully']);
	}

	public function syncCurrencies(Location $location, Request $request): JsonResponse
	{
		$user = $request->user();

		if (!$location->canManageAdmins($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$currencyIds = $request->input('currency_ids', []);

		$location->currencies()->sync($currencyIds);

		return response()->json(['message' => 'Currencies updated successfully']);
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
			'user_id' => $location->user_id,
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
