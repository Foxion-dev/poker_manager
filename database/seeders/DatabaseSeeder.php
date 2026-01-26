<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Tournament;
use App\Models\User;
use App\Models\UserRoom;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	public function run(): void
	{
		$rooms = Room::factory()->count(8)->create();

		$user = User::factory()->create([
			'name' => 'Test User',
			'email' => 'test@example.com',
			'balance' => 1000.00,
		]);

		foreach ($rooms as $room) {
			UserRoom::create([
				'user_id' => $user->id,
				'room_id' => $room->id,
				'balance' => fake()->randomFloat(2, 100, 500),
			]);
		}

		Tournament::factory()->count(50)->create([
			'user_id' => $user->id,
		]);

		$additionalUsers = User::factory()->count(3)->create();

		foreach ($additionalUsers as $additionalUser) {
			$selectedRooms = $rooms->random(rand(3, 6));
			foreach ($selectedRooms as $selectedRoom) {
				UserRoom::create([
					'user_id' => $additionalUser->id,
					'room_id' => $selectedRoom->id,
					'balance' => fake()->randomFloat(2, 50, 300),
				]);
			}

			Tournament::factory()->count(rand(20, 40))->create([
				'user_id' => $additionalUser->id,
			]);
		}
	}
}
