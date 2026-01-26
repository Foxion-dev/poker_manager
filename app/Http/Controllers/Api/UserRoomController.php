<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateUserRoomBalanceRequest;
use App\Models\Room;
use App\Models\UserRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRoomController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$userRooms = $request->user()->userRooms()->with('room')->get();

		return response()->json($userRooms);
	}

	public function show(Request $request, Room $room): JsonResponse
	{
		$userRoom = $request->user()->userRooms()
			->where('room_id', $room->id)
			->with('room')
			->firstOrFail();

		return response()->json($userRoom);
	}

	public function updateBalance(Request $request, Room $room, UpdateUserRoomBalanceRequest $updateRequest): JsonResponse
	{
		$userRoom = $request->user()->userRooms()
			->where('room_id', $room->id)
			->firstOrFail();

		$userRoom->update($updateRequest->validated());

		return response()->json($userRoom->load('room'));
	}

	public function attach(Request $request, Room $room): JsonResponse
	{
		$userRoom = $request->user()->userRooms()
			->where('room_id', $room->id)
			->first();

		if ($userRoom) {
			return response()->json(['message' => 'User already attached to this room'], 400);
		}

		$userRoom = UserRoom::create([
			'user_id' => $request->user()->id,
			'room_id' => $room->id,
			'balance' => 0,
		]);

		return response()->json($userRoom->load('room'), 201);
	}

	public function detach(Request $request, Room $room): JsonResponse
	{
		$userRoom = $request->user()->userRooms()
			->where('room_id', $room->id)
			->firstOrFail();

		$userRoom->delete();

		return response()->json(['message' => 'User detached from room successfully']);
	}
}
