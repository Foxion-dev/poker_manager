<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePersonalRoomRequest;
use App\Http\Requests\Api\StoreRoomRequest;
use App\Http\Requests\Api\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$priorityOrder = [
			'GGPoker' => 1,
			'pokerdom' => 2,
			'redstarpoker' => 3,
			'coinpoker' => 4,
		];

		$query = Room::with(['currency', 'currencies']);
		if ($request->user()) {
			$query->whereNull('user_id')->orWhere('user_id', $request->user()->id);
		} else {
			$query->whereNull('user_id');
		}

		$rooms = $query->get()
			->unique(fn (Room $room) => $room->user_id ? $room->id : $room->name)
			->sortBy(function (Room $room) use ($priorityOrder) {
				$isPersonal = $room->user_id !== null;
				return [
					$isPersonal ? 1 : 0,
					$isPersonal ? $room->name : ($priorityOrder[$room->name] ?? 999),
				];
			})
			->values();

		return response()->json($rooms);
	}

	public function storePersonal(StorePersonalRoomRequest $request): JsonResponse
	{
		$data = [
			'user_id' => $request->user()->id,
			'name' => $request->validated('name'),
			'icon' => $request->validated('icon'),
			'currency_id' => $request->validated('currency_id'),
		];

		if ($request->hasFile('image')) {
			$data['image'] = $request->file('image')->store('rooms', 'public');
		}

		$room = Room::create($data);

		$currencyIds = $request->validated('currency_ids', []);
		if (!empty($currencyIds)) {
			$room->currencies()->sync($currencyIds);
		}

		return response()->json($room->load(['currency', 'currencies']), 201);
	}

	public function destroyPersonal(Request $request, Room $room): JsonResponse
	{
		if ($room->user_id !== $request->user()->id) {
			return response()->json(['message' => 'Forbidden'], 403);
		}

		if ($room->image) {
			Storage::disk('public')->delete($room->image);
		}

		$room->delete();

		return response()->json(['message' => 'Room deleted successfully']);
	}

	public function store(StoreRoomRequest $request): JsonResponse
	{
		$data = $request->validated();
		$currencyIds = $data['currency_ids'] ?? [];
		unset($data['currency_ids']);

		if ($request->hasFile('image')) {
			$data['image'] = $request->file('image')->store('rooms', 'public');
		}

		$room = Room::create($data);

		if (!empty($currencyIds)) {
			$room->currencies()->sync($currencyIds);
		}

		return response()->json($room->load(['currency', 'currencies']), 201);
	}

	public function show(Room $room): JsonResponse
	{
		return response()->json($room->load(['currency', 'currencies']));
	}

	public function update(UpdateRoomRequest $request, Room $room): JsonResponse
	{
		$data = $request->validated();
		$currencyIds = $data['currency_ids'] ?? null;
		unset($data['currency_ids']);

		if ($request->hasFile('image')) {
			if ($room->image) {
				Storage::disk('public')->delete($room->image);
			}
			$data['image'] = $request->file('image')->store('rooms', 'public');
		}

		$room->update($data);

		if ($currencyIds !== null) {
			$room->currencies()->sync($currencyIds);
		}

		return response()->json($room->load(['currency', 'currencies']));
	}

	public function destroy(Room $room): JsonResponse
	{
		if ($room->image) {
			Storage::disk('public')->delete($room->image);
		}

		$room->delete();

		return response()->json(['message' => 'Room deleted successfully']);
	}
}
