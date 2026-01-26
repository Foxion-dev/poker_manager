<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreRoomRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

		$rooms = Room::all()
			->unique('name')
			->sortBy(function ($room) use ($priorityOrder) {
				return $priorityOrder[$room->name] ?? 999;
			})
			->values();

		return response()->json($rooms);
	}

	public function store(StoreRoomRequest $request): JsonResponse
	{
		$room = Room::create($request->validated());

		return response()->json($room, 201);
	}

	public function show(Room $room): JsonResponse
	{
		return response()->json($room);
	}

	public function update(Request $request, Room $room): JsonResponse
	{
		$validated = $request->validate([
			'name' => ['sometimes', 'required', 'string', 'max:255'],
			'icon' => ['nullable', 'string', 'max:10'],
		]);

		$room->update($validated);

		return response()->json($room);
	}

	public function destroy(Room $room): JsonResponse
	{
		$room->delete();

		return response()->json(['message' => 'Room deleted successfully']);
	}
}
