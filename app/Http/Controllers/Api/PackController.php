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
			->with('currency')
			->orderBy('start_date', 'desc')
			->get()
			->map(function ($pack) {
				return [
					'id' => $pack->id,
					'name' => $pack->name,
					'start_date' => $pack->start_date->format('Y-m-d'),
					'end_date' => $pack->end_date?->format('Y-m-d'),
					'buyin' => $pack->buyin,
					'cashout' => $pack->cashout,
					'currency' => $pack->currency,
					'description' => $pack->description,
					'buyin_usd' => $pack->buyin_usd,
					'cashout_usd' => $pack->cashout_usd,
					'profit_usd' => $pack->profit_usd,
					'roi' => $pack->roi,
					'is_itm' => $pack->is_itm,
				];
			});

		return response()->json($packs);
	}

	public function store(StorePackRequest $request): JsonResponse
	{
		$user = $request->user();
		$pack = $user->packs()->create($request->validated());

		return response()->json($pack->load('currency'), 201);
	}

	public function show(Pack $pack, Request $request): JsonResponse
	{
		$user = $request->user();

		if ($pack->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$pack->load('currency');

		return response()->json([
			'id' => $pack->id,
			'name' => $pack->name,
			'start_date' => $pack->start_date->format('Y-m-d'),
			'end_date' => $pack->end_date?->format('Y-m-d'),
			'buyin' => $pack->buyin,
			'cashout' => $pack->cashout,
			'currency' => $pack->currency,
			'description' => $pack->description,
			'buyin_usd' => $pack->buyin_usd,
			'cashout_usd' => $pack->cashout_usd,
			'profit_usd' => $pack->profit_usd,
			'roi' => $pack->roi,
			'is_itm' => $pack->is_itm,
		]);
	}

	public function update(UpdatePackRequest $request, Pack $pack): JsonResponse
	{
		$user = $request->user();

		if ($pack->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$pack->update($request->validated());
		$pack->load('currency');

		return response()->json($pack);
	}

	public function destroy(Pack $pack, Request $request): JsonResponse
	{
		$user = $request->user();

		if ($pack->user_id !== $user->id) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$pack->delete();

		return response()->json(['message' => 'Pack deleted successfully']);
	}
}
