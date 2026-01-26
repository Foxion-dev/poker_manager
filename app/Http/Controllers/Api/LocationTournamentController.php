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
					'itm_percentage' => $tournament->itm_percentage ?? 15,
					'rake' => $tournament->rake ?? 30,
					'rake_type' => $tournament->rake_type ?? 'fixed',
					'date' => $tournament->date->format('Y-m-d'),
					'is_finished' => $tournament->is_finished ?? false,
					'total_buyin' => $tournament->total_buyin,
					'prize_pool' => $tournament->prize_pool,
					'prize_distribution' => $tournament->prize_distribution,
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
		
		if (!isset($data['prize_distribution']) || empty($data['prize_distribution'])) {
			unset($data['prize_distribution']);
		}

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

		if (!$location->is_public && $location->user_id !== $user->id && !$location->isAdmin($user)) {
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
			'itm_percentage' => $locationTournament->itm_percentage ?? 15,
			'rake' => $locationTournament->rake ?? 30,
			'rake_type' => $locationTournament->rake_type ?? 'fixed',
			'prize_distribution' => $locationTournament->prize_distribution,
			'date' => $locationTournament->date->format('Y-m-d'),
			'location' => $location,
			'is_finished' => $locationTournament->is_finished ?? false,
			'total_buyin' => $locationTournament->total_buyin,
			'prize_pool' => $locationTournament->prize_pool,
			'prize_distribution' => $locationTournament->prize_distribution,
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
		
		if (isset($data['prize_distribution'])) {
			if (empty($data['prize_distribution'])) {
				$data['prize_distribution'] = null;
			}
		} else {
			unset($data['prize_distribution']);
		}

		$locationTournament->update($data);
		$locationTournament->load('participants.user');

		return response()->json($locationTournament);
	}

	public function destroy(Location $location, $locationTournamentId): JsonResponse
	{
		$user = request()->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

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
					'is_paid' => $participantData['is_paid'] ?? false,
					'prize' => $participantData['prize'] ?? null,
				]);
			}
		}

		$locationTournament->load(['participants.user', 'currency']);

		return response()->json($locationTournament);
	}

	public function addParticipant(Request $request, Location $location, $locationTournamentId): JsonResponse
	{
		$user = $request->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can add participants'], 403);
		}

		if ($locationTournament->is_finished) {
			return response()->json(['message' => 'Cannot add participants to finished tournament'], 400);
		}

		$request->validate([
			'user_id' => 'nullable|exists:users,id',
			'name' => 'nullable|string|max:255',
		], [
			'user_id.exists' => 'Пользователь не найден',
		]);

		if (!$request->user_id && !$request->name) {
			return response()->json(['message' => 'Необходимо указать либо пользователя, либо имя участника'], 422);
		}

		$maxPlace = $locationTournament->participants()->max('place') ?? 0;
		$newPlace = $maxPlace + 1;

		$participantData = [
			'user_id' => $request->user_id ?: null,
			'name' => $request->name ?: null,
			'place' => $newPlace,
			'rebuy' => 0,
			'addon' => false,
			'is_paid' => false,
		];

		$participant = $locationTournament->participants()->create($participantData);

		if ($request->user_id) {
			$locationUser = $location->locationUsers()->where('user_id', $request->user_id)->first();
			if (!$locationUser) {
				$location->locationUsers()->create([
					'user_id' => $request->user_id,
				]);
			}
		} elseif ($request->name) {
			$locationUser = $location->locationUsers()->where('name', $request->name)->whereNull('user_id')->first();
			if (!$locationUser) {
				$location->locationUsers()->create([
					'name' => $request->name,
				]);
			}
		}

		$locationTournament->load(['participants.user', 'currency']);

		return response()->json($locationTournament);
	}

	public function removeParticipant(Request $request, Location $location, $locationTournamentId, $participantId): JsonResponse
	{
		$user = $request->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can remove participants'], 403);
		}

		if ($locationTournament->is_finished) {
			return response()->json(['message' => 'Cannot remove participants from finished tournament'], 400);
		}

		$participant = $locationTournament->participants()->findOrFail($participantId);
		$participant->delete();

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

		$unpaidParticipants = $locationTournament->participants()
			->where('is_paid', false)
			->count();

		if ($unpaidParticipants > 0) {
			return response()->json([
				'message' => 'Нельзя завершить турнир: не все участники оплатили вход',
				'unpaid_count' => $unpaidParticipants,
			], 422);
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
