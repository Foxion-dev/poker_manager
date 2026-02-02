<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserDisabledRoomController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$roomIds = $request->user()->getDisabledRoomIds();

		return response()->json(['room_ids' => $roomIds]);
	}

	public function store(Request $request, Room $room): JsonResponse
	{
		$settings = $request->user()->getSettings();
		$ids = $settings->getDisabledRoomIds();
		if (in_array($room->id, $ids, true)) {
			return response()->json(['message' => 'Room already disabled'], 400);
		}
		$settings->addDisabledRoomId($room->id);

		return response()->json(['message' => 'Room disabled'], 201);
	}

	public function destroy(Request $request, Room $room): JsonResponse
	{
		$removed = $request->user()->getSettings()->removeDisabledRoomId($room->id);

		if (!$removed) {
			return response()->json(['message' => 'Room was not disabled'], 404);
		}

		return response()->json(['message' => 'Room enabled']);
	}
}
