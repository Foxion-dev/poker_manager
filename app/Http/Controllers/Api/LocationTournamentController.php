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

		if (!$location->is_public && $location->user_id !== $user->id && !$location->isAdmin($user)) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		if ($location->is_public && $location->password) {
			$password = $request->get('password');
			
			if (!$password) {
				$savedPassword = \App\Models\LocationUserPassword::where('user_id', $user->id)
					->where('location_id', $location->id)
					->first();
				
				if ($savedPassword) {
					$password = $savedPassword->password;
					if (!$location->checkPassword($password)) {
						if ($location->user_id !== $user->id && !$location->isAdmin($user)) {
							return response()->json(['message' => 'Требуется пароль', 'requires_password' => true], 403);
						}
					}
				} elseif ($location->user_id !== $user->id && !$location->isAdmin($user)) {
					return response()->json(['message' => 'Требуется пароль', 'requires_password' => true], 403);
				}
			} else {
				if (!$location->checkPassword($password)) {
					if ($location->user_id !== $user->id && !$location->isAdmin($user)) {
						return response()->json(['message' => 'Требуется пароль', 'requires_password' => true], 403);
					}
				} else {
					\App\Models\LocationUserPassword::updateOrCreate(
						[
							'user_id' => $user->id,
							'location_id' => $location->id,
						],
						['password' => $password]
					);
				}
			}
		}

		$tournaments = $location->tournaments()
			->with(['participants.user', 'currency'])
			->orderBy('date', 'desc')
			->get()
			->map(function ($tournament) {
				return [
					'id' => $tournament->id,
					'name' => $tournament->display_name,
					'buyin' => $tournament->buyin,
					'bounty' => $tournament->bounty,
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
					'bounty_pool' => $tournament->bounty_pool,
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
							'is_paid' => $participant->is_paid ?? false,
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
			'name' => $locationTournament->display_name,
			'buyin' => $locationTournament->buyin,
			'bounty' => $locationTournament->bounty,
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
			'bounty_pool' => $locationTournament->bounty_pool,
			'bounty_pool_remaining' => $locationTournament->bounty_pool_remaining,
			'bounty_pool_taken' => $locationTournament->bounty_pool_taken,
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
					'is_paid' => $participant->is_paid ?? false,
					'prize' => $participant->prize,
					'bounty_stack' => $participant->bounty_stack,
					'bounty_prize' => $participant->bounty_prize,
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
				$newRebuy = $participantData['rebuy'] ?? 0;

				$updateData = [
					'rebuy' => $newRebuy,
					'addon' => $participantData['addon'] ?? false,
					'is_paid' => $participantData['is_paid'] ?? false,
					'prize' => $participantData['prize'] ?? null,
				];

				if ($locationTournament->format === 'progressive_bounty' && $newRebuy > ($participant->rebuy ?? 0)) {
					$currentStack = (float) ($participant->bounty_stack ?? 0);
					$bountyAmount = (float) ($locationTournament->bounty ?? 0);

					if ($bountyAmount > 0) {
						if ($currentStack <= 0) {
							$updateData['bounty_stack'] = $bountyAmount;
						} else {
							$updateData['bounty_stack'] = $currentStack + $bountyAmount;
						}
					}
				}

				$participant->update($updateData);
			}
		}

		$locationTournament->load(['participants.user', 'currency']);
		
		return response()->json([
			'participants' => $locationTournament->participants,
			'total_buyin' => $locationTournament->total_buyin,
			'prize_pool' => $locationTournament->prize_pool,
			'bounty_pool' => $locationTournament->bounty_pool,
			'bounty_pool_remaining' => $locationTournament->bounty_pool_remaining,
			'bounty_pool_taken' => $locationTournament->bounty_pool_taken,
			'prize_distribution' => $locationTournament->prize_distribution,
		]);
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
		
		$usersFromSystem = $location->users()->get()->map(function($user) use ($location) {
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
				'user_id' => $user->id,
				'name' => null,
				'display_name' => $user->name,
				'tournaments_count' => $tournamentsCount,
			];
		});
		
		$usersByName = $location->locationUsers()->whereNull('user_id')->get()->map(function($locationUser) {
			return [
				'id' => $locationUser->id,
				'user_id' => null,
				'name' => $locationUser->name,
				'display_name' => $locationUser->name,
				'tournaments_count' => $locationUser->tournaments_count,
			];
		});
		
		$allLocationUsers = $usersFromSystem->concat($usersByName)->sortByDesc('tournaments_count')->values();

		return response()->json([
			'participants' => $locationTournament->participants,
			'total_buyin' => $locationTournament->total_buyin,
			'prize_pool' => $locationTournament->prize_pool,
			'bounty_pool' => $locationTournament->bounty_pool,
			'bounty_pool_remaining' => $locationTournament->bounty_pool_remaining,
			'bounty_pool_taken' => $locationTournament->bounty_pool_taken,
			'prize_distribution' => $locationTournament->prize_distribution,
			'users' => $allLocationUsers->values(),
		]);
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
		
		$usersFromSystem = $location->users()->get()->map(function($user) use ($location) {
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
				'user_id' => $user->id,
				'name' => null,
				'display_name' => $user->name,
				'tournaments_count' => $tournamentsCount,
			];
		});
		
		$usersByName = $location->locationUsers()->whereNull('user_id')->get()->map(function($locationUser) {
			return [
				'id' => $locationUser->id,
				'user_id' => null,
				'name' => $locationUser->name,
				'display_name' => $locationUser->name,
				'tournaments_count' => $locationUser->tournaments_count,
			];
		});
		
		$allLocationUsers = $usersFromSystem->concat($usersByName)->sortByDesc('tournaments_count')->values();

		return response()->json([
			'participants' => $locationTournament->participants,
			'total_buyin' => $locationTournament->total_buyin,
			'prize_pool' => $locationTournament->prize_pool,
			'bounty_pool' => $locationTournament->bounty_pool,
			'bounty_pool_remaining' => $locationTournament->bounty_pool_remaining,
			'bounty_pool_taken' => $locationTournament->bounty_pool_taken,
			'prize_distribution' => $locationTournament->prize_distribution,
			'users' => $allLocationUsers->values(),
		]);
	}

	public function progressiveBountyHit(Request $request, Location $location, $locationTournamentId): JsonResponse
	{
		$user = $request->user();
		$locationTournament = LocationTournament::where('id', $locationTournamentId)
			->where('location_id', $location->id)
			->firstOrFail();

		if (!$location->isAdmin($user)) {
			return response()->json(['message' => 'Only location admins can update progressive bounty'], 403);
		}

		if ($locationTournament->is_finished) {
			return response()->json(['message' => 'Cannot update progressive bounty for finished tournament'], 400);
		}

		if ($locationTournament->format !== 'progressive_bounty') {
			return response()->json(['message' => 'Tournament is not progressive bounty'], 400);
		}

		$data = $request->validate([
			'killer_participant_id' => ['required', 'integer'],
			'victim_participant_id' => ['required', 'integer', 'different:killer_participant_id'],
		]);

		$killer = $locationTournament->participants()->where('id', $data['killer_participant_id'])->firstOrFail();
		$victim = $locationTournament->participants()->where('id', $data['victim_participant_id'])->firstOrFail();

		$currentStack = $victim->bounty_stack ?? $locationTournament->bounty ?? 0;
		$currentStack = (float) $currentStack;

		if ($currentStack <= 0) {
			return response()->json(['message' => 'Victim has no bounty stack'], 422);
		}

		$half = $currentStack / 2;
		$toPocket = floor($half / 5) * 5;
		$toStack = $currentStack - $toPocket;

		$killer->bounty_prize = ($killer->bounty_prize ?? 0) + $toPocket;
		$killer->bounty_stack = ($killer->bounty_stack ?? ($locationTournament->bounty ?? 0)) + $toStack;
		$victim->bounty_stack = 0;

		$killer->save();
		$victim->save();

		$locationTournament->load(['participants.user', 'currency']);

		return response()->json([
			'participants' => $locationTournament->participants,
			'total_buyin' => $locationTournament->total_buyin,
			'prize_pool' => $locationTournament->prize_pool,
			'bounty_pool' => $locationTournament->bounty_pool,
			'prize_distribution' => $locationTournament->prize_distribution,
		]);
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

		$user = $request->user();

		if ($location->password) {
			$isOwnerOrAdmin = $user && ($location->user_id === $user->id || $location->isAdmin($user));
			
			if (!$isOwnerOrAdmin) {
				$password = $request->get('password');
				
				if (!$password) {
					if ($user) {
						$savedPassword = \App\Models\LocationUserPassword::where('user_id', $user->id)
							->where('location_id', $location->id)
							->first();
						
						if ($savedPassword) {
							$password = $savedPassword->password;
							if (!$location->checkPassword($password)) {
								return response()->json(['message' => 'Требуется пароль', 'requires_password' => true], 403);
							}
						} else {
							return response()->json(['message' => 'Требуется пароль', 'requires_password' => true], 403);
						}
					} else {
						return response()->json(['message' => 'Требуется пароль', 'requires_password' => true], 403);
					}
				} else {
					if (!$location->checkPassword($password)) {
						return response()->json(['message' => 'Неверный пароль', 'requires_password' => true], 403);
					}
					
					if ($user) {
						\App\Models\LocationUserPassword::updateOrCreate(
							[
								'user_id' => $user->id,
								'location_id' => $location->id,
							],
							['password' => $password]
						);
					}
				}
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
					'name' => $tournament->display_name,
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
							'rebuy' => $participant->rebuy ?? 0,
							'addon' => $participant->addon ?? false,
							'is_paid' => $participant->is_paid ?? false,
							'prize' => $participant->prize,
							'display_name' => $participant->display_name,
						];
					})->sortBy('place')->values(),
				];
			});

		return response()->json($tournaments);
	}
}
