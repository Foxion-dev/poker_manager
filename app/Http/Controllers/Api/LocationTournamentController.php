<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLocationTournamentRequest;
use App\Http\Requests\Api\UpdateLocationTournamentRequest;
use App\Models\Location;
use App\Models\LocationTournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationTournamentController extends Controller
{
	public function index(Request $request, Location $location): JsonResponse
	{
		$user = $request->user();

		if (!$location->is_public && $location->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$tournaments = $location->tournaments()
			->with('participants.user')
			->orderBy('date', 'desc')
			->get()
			->map(function ($tournament) {
				return [
					'id' => $tournament->id,
					'name' => $tournament->name,
					'buyin' => $tournament->buyin,
					'format' => $tournament->format,
					'format_label' => $tournament->format_label,
					'date' => $tournament->date->format('Y-m-d'),
					'participants' => $tournament->participants->map(function ($participant) {
						return [
							'id' => $participant->id,
							'name' => $participant->name,
							'user_id' => $participant->user_id,
							'user' => $participant->user,
							'place' => $participant->place,
							'prize' => $participant->prize,
							'display_name' => $participant->display_name,
						];
					})->sortBy('place')->values(),
				];
			});

		return response()->json($tournaments);
	}

	public function store(StoreLocationTournamentRequest $request, Location $location): JsonResponse
	{
		$user = $request->user();

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can create tournaments'], 403);
		}

		$data = $request->validated();
		$participants = $data['participants'];
		unset($data['participants']);

		$tournament = $location->tournaments()->create($data);

		foreach ($participants as $participant) {
			$tournament->participants()->create($participant);
		}

		return response()->json($tournament->load('participants.user'), 201);
	}

	public function show(LocationTournament $locationTournament, Request $request): JsonResponse
	{
		$user = $request->user();
		$location = $locationTournament->location;

		if (!$location->is_public && $location->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$locationTournament->load('participants.user');

		return response()->json([
			'id' => $locationTournament->id,
			'name' => $locationTournament->name,
			'buyin' => $locationTournament->buyin,
			'format' => $locationTournament->format,
			'format_label' => $locationTournament->format_label,
			'date' => $locationTournament->date->format('Y-m-d'),
			'location' => $location,
			'participants' => $locationTournament->participants->map(function ($participant) {
				return [
					'id' => $participant->id,
					'name' => $participant->name,
					'user_id' => $participant->user_id,
					'user' => $participant->user,
					'place' => $participant->place,
					'prize' => $participant->prize,
					'display_name' => $participant->display_name,
				];
			})->sortBy('place')->values(),
		]);
	}

	public function update(UpdateLocationTournamentRequest $request, LocationTournament $locationTournament): JsonResponse
	{
		$user = $request->user();
		$location = $locationTournament->location;

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can update tournaments'], 403);
		}

		$data = $request->validated();

		if (isset($data['participants'])) {
			$participants = $data['participants'];
			unset($data['participants']);

			$locationTournament->participants()->delete();

			foreach ($participants as $participant) {
				$locationTournament->participants()->create($participant);
			}
		}

		$locationTournament->update($data);
		$locationTournament->load('participants.user');

		return response()->json($locationTournament);
	}

	public function destroy(LocationTournament $locationTournament, Request $request): JsonResponse
	{
		$user = $request->user();
		$location = $locationTournament->location;

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can delete tournaments'], 403);
		}

		$locationTournament->delete();

		return response()->json(['message' => 'Tournament deleted successfully']);
	}

	public function publicIndex(Request $request, Location $location): JsonResponse
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

		$limit = $request->get('limit', 10);
		$tournaments = $location->tournaments()
			->with('participants.user')
			->orderBy('date', 'desc')
			->limit($limit)
			->get()
			->map(function ($tournament) {
				return [
					'id' => $tournament->id,
					'name' => $tournament->name,
					'buyin' => $tournament->buyin,
					'format' => $tournament->format,
					'format_label' => $tournament->format_label,
					'date' => $tournament->date->format('Y-m-d'),
					'participants' => $tournament->participants->map(function ($participant) {
						return [
							'id' => $participant->id,
							'user' => [
								'id' => $participant->user->id,
								'name' => $participant->user->name,
							],
							'place' => $participant->place,
							'prize' => $participant->prize,
						];
					})->sortBy('place')->values(),
				];
			});

		return response()->json($tournaments);
	}
}
