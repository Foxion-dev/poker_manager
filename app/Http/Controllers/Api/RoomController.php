<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreRoomRequest;
use App\Http\Requests\Api\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
	public function index(): JsonResponse
	{
		$priorityOrder = [
			'GGPoker' => 1,
			'pokerdom' => 2,
			'redstarpoker' => 3,
			'coinpoker' => 4,
		];

		$rooms = Room::with(['currency', 'currencies'])
			->get()
			->unique('name')
			->sortBy(function ($room) use ($priorityOrder) {
				return $priorityOrder[$room->name] ?? 999;
			})
			->values();

		return response()->json($rooms);
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
