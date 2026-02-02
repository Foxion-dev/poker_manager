<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\UserRoomSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserDisabledRoomController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$roomIds = $request->user()->disabledRooms()->pluck('room_id');

		return response()->json(['room_ids' => $roomIds]);
	}

	public function store(Request $request, Room $room): JsonResponse
	{
		$user = $request->user();
		$exists = UserRoomSetting::where('user_id', $user->id)->where('room_id', $room->id)->exists();

		if ($exists) {
			return response()->json(['message' => 'Room already disabled'], 400);
		}

		UserRoomSetting::create([
			'user_id' => $user->id,
			'room_id' => $room->id,
		]);

		return response()->json(['message' => 'Room disabled'], 201);
	}

	public function destroy(Request $request, Room $room): JsonResponse
	{
		$deleted = UserRoomSetting::where('user_id', $request->user()->id)
			->where('room_id', $room->id)
			->delete();

		if (!$deleted) {
			return response()->json(['message' => 'Room was not disabled'], 404);
		}

		return response()->json(['message' => 'Room enabled']);
	}
}
