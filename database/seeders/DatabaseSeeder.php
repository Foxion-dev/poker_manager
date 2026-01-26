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
		$this->call([
			CurrencySeeder::class,
			AspenLocationSeeder::class,
		]);

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
				'is_admin' => true,
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

		$usdCurrency = \App\Models\Currency::where('code', 'USD')->first();
		
		if (!$usdCurrency) {
			logger()->warning('USD currency not found in seeder');
		}
		
		$buyins = [5.50, 11, 22, 33, 55, 109, 215, 530];
		$startDate = now()->subYear()->startOfMonth();
		$endDate = now();
		$tournamentCount = 0;
		$totalProfit = 0;

		Tournament::where('user_id', $testUser->id)->delete();

		$currentDate = $startDate->copy();
		
		while ($currentDate->lte($endDate) && $tournamentCount < 500) {
			if ($currentDate->isWeekend()) {
				$tournamentsPerDay = rand(2, 6);
			} else {
				$tournamentsPerDay = rand(1, 4);
			}

			if ($currentDate->isFriday() || $currentDate->isSaturday()) {
				$tournamentsPerDay = rand(3, 8);
			}

			for ($j = 0; $j < $tournamentsPerDay && $tournamentCount < 500; $j++) {
				$room = $rooms->random();
				$buyin = fake()->randomElement($buyins);
				$bountyCount = fake()->numberBetween(0, 4);
				$totalBuyin = $buyin + ($bountyCount * $buyin);

				$place = null;
				$cashout = null;
				$playersCount = fake()->numberBetween(50, 5000);

				$itmChance = fake()->numberBetween(1, 100);
				if ($itmChance <= 18) {
					$place = fake()->numberBetween(1, (int)($playersCount * 0.15));
					$prizeMultiplier = fake()->randomFloat(2, 1.2, 20);
					
					if ($place === 1) {
						$prizeMultiplier = fake()->randomFloat(2, 15, 50);
					} elseif ($place <= 3) {
						$prizeMultiplier = fake()->randomFloat(2, 8, 30);
					} elseif ($place <= 10) {
						$prizeMultiplier = fake()->randomFloat(2, 5, 20);
					} elseif ($place <= 30) {
						$prizeMultiplier = fake()->randomFloat(2, 2.5, 10);
					} elseif ($place <= 50) {
						$prizeMultiplier = fake()->randomFloat(2, 1.5, 5);
					}
					
					$cashout = $totalBuyin * $prizeMultiplier;
					$totalProfit += ($cashout - $totalBuyin);
				} else {
					$place = fake()->numberBetween((int)($playersCount * 0.15) + 1, $playersCount);
					$totalProfit -= $totalBuyin;
				}

				Tournament::create([
					'user_id' => $testUser->id,
					'room_id' => $room->id,
					'currency_id' => $usdCurrency?->id,
					'buyin' => $buyin,
					'date' => $currentDate->format('Y-m-d'),
					'place' => $place,
					'cashout' => $cashout,
					'bounty_count' => $bountyCount,
					'players_count' => $playersCount,
					'created_at' => $currentDate->copy()->addHours(rand(10, 23))->addMinutes(rand(0, 59)),
					'updated_at' => $currentDate->copy()->addHours(rand(10, 23))->addMinutes(rand(0, 59)),
				]);

				$tournamentCount++;
			}
			
			$currentDate->addDay();
		}
		
		logger()->warning(sprintf('Created %d tournaments for test user (id=%d)', $tournamentCount, $testUser->id));

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
