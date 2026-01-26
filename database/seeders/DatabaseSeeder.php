<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Tournament;
use App\Models\User;
use App\Models\UserRoom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
	public function run(): void
	{
		$roomsData = [
			['name' => 'PokerStars', 'icon' => '🎰'],
			['name' => '888poker', 'icon' => '🃏'],
			['name' => 'partypoker', 'icon' => '🎲'],
			['name' => 'GGPoker', 'icon' => '♠️'],
			['name' => 'Winamax', 'icon' => '♥️'],
			['name' => 'Unibet', 'icon' => '♦️'],
			['name' => 'Bet365', 'icon' => '♣️'],
			['name' => 'ACR', 'icon' => '🎴'],
			['name' => 'pokerdom', 'icon' => '🎯'],
			['name' => 'redstarpoker', 'icon' => '⭐'],
			['name' => 'coinpoker', 'icon' => '🪙'],
		];

		$rooms = collect();
		foreach ($roomsData as $roomData) {
			$rooms->push(Room::firstOrCreate(
				['name' => $roomData['name']],
				$roomData
			));
		}

		$testUser = User::firstOrCreate(
			['email' => 'test@example.com'],
			[
				'name' => 'Test User',
				'password' => Hash::make('password'),
				'balance' => 2500.75,
			]
		);

		$userRoomBalances = [
			'PokerStars' => 450.50,
			'888poker' => 320.25,
			'partypoker' => 180.00,
			'GGPoker' => 550.75,
			'Winamax' => 280.50,
			'Unibet' => 195.25,
			'Bet365' => 320.00,
			'ACR' => 203.50,
			'pokerdom' => 380.00,
			'redstarpoker' => 275.50,
			'coinpoker' => 420.25,
		];

		foreach ($rooms as $room) {
			UserRoom::firstOrCreate(
				[
					'user_id' => $testUser->id,
					'room_id' => $room->id,
				],
				[
					'balance' => $userRoomBalances[$room->name] ?? fake()->randomFloat(2, 100, 500),
				]
			);
		}

		$buyins = [5.50, 11, 22, 33, 55, 109, 215, 530];
		$startDate = now()->subMonths(6);
		$tournamentCount = 0;
		$totalProfit = 0;

		Tournament::where('user_id', $testUser->id)->delete();

		for ($i = 0; $i < 180; $i++) {
			$date = $startDate->copy()->addDays($i);
			if ($date->isWeekend()) {
				$tournamentsPerDay = rand(2, 5);
			} else {
				$tournamentsPerDay = rand(1, 3);
			}

			for ($j = 0; $j < $tournamentsPerDay && $tournamentCount < 200; $j++) {
				$room = $rooms->random();
				$buyin = fake()->randomElement($buyins);
				$bountyCount = fake()->numberBetween(0, 3);
				$totalBuyin = $buyin + ($bountyCount * $buyin);

				$place = null;
				$cashout = null;

				$itmChance = fake()->numberBetween(1, 100);
				if ($itmChance <= 15) {
					$place = fake()->numberBetween(1, 100);
					$prizeMultiplier = fake()->randomFloat(2, 1.2, 15);
					if ($place <= 10) {
						$prizeMultiplier = fake()->randomFloat(2, 5, 25);
					} elseif ($place <= 30) {
						$prizeMultiplier = fake()->randomFloat(2, 3, 10);
					}
					$cashout = $totalBuyin * $prizeMultiplier;
					$totalProfit += ($cashout - $totalBuyin);
				} else {
					$place = fake()->numberBetween(101, 1000);
					$totalProfit -= $totalBuyin;
				}

				Tournament::create([
					'user_id' => $testUser->id,
					'room_id' => $room->id,
					'buyin' => $buyin,
					'date' => $date->format('Y-m-d'),
					'place' => $place,
					'cashout' => $cashout,
					'bounty_count' => $bountyCount,
					'created_at' => $date->copy()->addHours(rand(10, 22)),
					'updated_at' => $date->copy()->addHours(rand(10, 22)),
				]);

				$tournamentCount++;
			}
		}

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
