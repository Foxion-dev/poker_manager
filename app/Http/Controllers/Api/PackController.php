<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePackRequest;
use App\Http\Requests\Api\UpdatePackRequest;
use App\Models\Pack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$user = $request->user();
		$packs = $user->packs()
			->withCount('tournaments')
			->orderBy('start_date', 'desc')
			->get()
			->map(function ($pack) {
				return [
					'id' => $pack->id,
					'name' => $pack->name,
					'start_date' => $pack->start_date->format('Y-m-d'),
					'end_date' => $pack->end_date?->format('Y-m-d'),
					'description' => $pack->description,
					'tournaments_count' => $pack->tournaments_count,
					'total_profit_usd' => $pack->total_profit_usd,
					'roi' => $pack->roi,
					'itm_percentage' => $pack->itm_percentage,
				];
			});

		return response()->json($packs);
	}

	public function store(StorePackRequest $request): JsonResponse
	{
		$user = $request->user();
		$pack = $user->packs()->create($request->validated());

		return response()->json($pack->loadCount('tournaments'), 201);
	}

	public function show(Pack $pack, Request $request): JsonResponse
	{
		$user = $request->user();

		if ($pack->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$pack->loadCount('tournaments');

		return response()->json([
			'id' => $pack->id,
			'name' => $pack->name,
			'start_date' => $pack->start_date->format('Y-m-d'),
			'end_date' => $pack->end_date?->format('Y-m-d'),
			'description' => $pack->description,
			'tournaments_count' => $pack->tournaments_count,
			'total_tournaments' => $pack->total_tournaments,
			'total_profit_usd' => $pack->total_profit_usd,
			'total_buyin_usd' => $pack->total_buyin_usd,
			'total_cashout_usd' => $pack->total_cashout_usd,
			'roi' => $pack->roi,
			'itm_count' => $pack->itm_count,
			'itm_percentage' => $pack->itm_percentage,
			'average_buyin_usd' => $pack->average_buyin_usd,
		]);
	}

	public function update(UpdatePackRequest $request, Pack $pack): JsonResponse
	{
		$user = $request->user();

		if ($pack->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$pack->update($request->validated());
		$pack->loadCount('tournaments');

		return response()->json($pack);
	}

	public function destroy(Pack $pack, Request $request): JsonResponse
	{
		$user = $request->user();

		if ($pack->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$pack->tournaments()->update(['pack_id' => null]);
		$pack->delete();

		return response()->json(['message' => 'Pack deleted successfully']);
	}
}
