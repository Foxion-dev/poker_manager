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
			->with(['participants.user', 'currency'])
			->orderBy('date', 'desc')
			->get()
			->map(function ($tournament) {
				return [
					'id' => $tournament->id,
					'name' => $tournament->name,
					'buyin' => $tournament->buyin,
					'currency_id' => $tournament->currency_id,
					'currency' => $tournament->currency,
					'format' => $tournament->format,
					'format_label' => $tournament->format_label,
					'date' => $tournament->date->format('Y-m-d'),
					'is_finished' => $tournament->is_finished ?? false,
					'participants' => $tournament->participants->map(function ($participant) {
						return [
							'id' => $participant->id,
							'name' => $participant->name,
							'user_id' => $participant->user_id,
							'user' => $participant->user,
							'place' => $participant->place,
							'rebuy' => $participant->rebuy ?? 0,
							'addon' => $participant->addon ?? false,
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
			$participantData = [
				'user_id' => $participant['user_id'] ?? null,
				'name' => $participant['name'] ?? null,
				'place' => $participant['place'],
				'rebuy' => $participant['rebuy'] ?? 0,
				'addon' => $participant['addon'] ?? false,
			];
			$tournament->participants()->create($participantData);
		}

		return response()->json($tournament->load('participants.user'), 201);
	}

	public function show(Location $location, $locationTournamentId, Request $request): JsonResponse
	{
		$user = $request->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

		if (!$location->is_public && $location->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$locationTournament->load(['participants.user', 'currency']);

		return response()->json([
			'id' => $locationTournament->id,
			'name' => $locationTournament->name,
			'buyin' => $locationTournament->buyin,
			'currency_id' => $locationTournament->currency_id,
			'currency' => $locationTournament->currency,
			'format' => $locationTournament->format,
			'format_label' => $locationTournament->format_label,
			'date' => $locationTournament->date->format('Y-m-d'),
			'location' => $location,
			'is_finished' => $locationTournament->is_finished ?? false,
			'participants' => $locationTournament->participants->map(function ($participant) {
				return [
					'id' => $participant->id,
					'name' => $participant->name,
					'user_id' => $participant->user_id,
					'user' => $participant->user,
					'place' => $participant->place,
					'rebuy' => $participant->rebuy ?? 0,
					'addon' => $participant->addon ?? false,
					'prize' => $participant->prize,
					'display_name' => $participant->display_name,
				];
			})->sortBy('place')->values(),
		]);
	}

	public function update(UpdateLocationTournamentRequest $request, Location $location, $locationTournamentId): JsonResponse
	{
		$user = $request->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can update tournaments'], 403);
		}

		$data = $request->validated();

		if (isset($data['participants'])) {
			$participants = $data['participants'];
			unset($data['participants']);

			$locationTournament->participants()->delete();

			foreach ($participants as $participant) {
				$participantData = [
					'user_id' => $participant['user_id'] ?? null,
					'name' => $participant['name'] ?? null,
					'place' => $participant['place'],
					'rebuy' => $participant['rebuy'] ?? 0,
					'addon' => $participant['addon'] ?? false,
				];
				$locationTournament->participants()->create($participantData);
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

	public function updateParticipants(Request $request, Location $location, $locationTournamentId): JsonResponse
	{
		$user = $request->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can update participants'], 403);
		}

		$participants = $request->input('participants', []);

		foreach ($participants as $participantData) {
			$participant = $locationTournament->participants()->find($participantData['id']);
			if ($participant) {
				$participant->update([
					'rebuy' => $participantData['rebuy'] ?? 0,
					'addon' => $participantData['addon'] ?? false,
					'prize' => $participantData['prize'] ?? null,
				]);
			}
		}

		$locationTournament->load(['participants.user', 'currency']);

		return response()->json($locationTournament);
	}

	public function finish(Request $request, Location $location, $locationTournamentId): JsonResponse
	{
		$user = $request->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can finish tournaments'], 403);
		}

		$locationTournament->update(['is_finished' => true]);

		$locationTournament->load(['participants.user', 'currency']);

		return response()->json($locationTournament);
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
			->with(['participants.user', 'currency'])
			->orderBy('date', 'desc')
			->limit($limit)
			->get()
			->map(function ($tournament) {
				return [
					'id' => $tournament->id,
					'name' => $tournament->name,
					'buyin' => $tournament->buyin,
					'currency_id' => $tournament->currency_id,
					'currency' => $tournament->currency,
					'format' => $tournament->format,
					'format_label' => $tournament->format_label,
					'date' => $tournament->date->format('Y-m-d'),
					'participants' => $tournament->participants->map(function ($participant) {
						return [
							'id' => $participant->id,
							'name' => $participant->name,
							'user_id' => $participant->user_id,
							'user' => $participant->user ? [
								'id' => $participant->user->id,
								'name' => $participant->user->name,
							] : null,
							'place' => $participant->place,
							'prize' => $participant->prize,
							'display_name' => $participant->display_name,
						];
					})->sortBy('place')->values(),
				];
			});

		return response()->json($tournaments);
	}
}
