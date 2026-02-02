<?php

namespace Database\Seeders;

use App\Models\Currency;
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
		$this->call(CurrencySeeder::class);

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

		$admin = User::firstOrCreate(
			['email' => 'mamama141996@gmail.com'],
			[
				'name' => 'mamama141996',
				'password' => Hash::make('password'),
				'balance' => 0,
				'is_admin' => true,
			]
		);

		$testUser = User::firstOrCreate(
			['email' => 'testuser@example.com'],
			[
				'name' => 'testuser',
				'password' => Hash::make('password'),
				'balance' => 0,
				'is_admin' => false,
			]
		);

		foreach ($rooms as $room) {
			UserRoom::firstOrCreate(
				['user_id' => $admin->id, 'room_id' => $room->id],
				['balance' => fake()->randomFloat(2, 0, 500)]
			);
			UserRoom::firstOrCreate(
				['user_id' => $testUser->id, 'room_id' => $room->id],
				['balance' => fake()->randomFloat(2, 100, 600)]
			);
		}

		$this->call(AspenLocationSeeder::class);

		$currencies = Currency::all();
		if ($currencies->isEmpty()) {
			return;
		}

		$buyins = [2.20, 5.50, 11, 22, 33, 55, 109, 215, 530];
		$startDate = now()->subYear();
		$endDate = now();
		$totalDays = $startDate->diffInDays($endDate) ?: 1;
		$tournamentsPerDay = (int) ceil(1000 / $totalDays);

		Tournament::where('user_id', $testUser->id)->delete();

		$created = 0;
		$currentDate = $startDate->copy();
		while ($created < 1000 && $currentDate->lte($endDate)) {
			$countToday = min($tournamentsPerDay + rand(-2, 2), 1000 - $created);
			$countToday = max(0, $countToday);

			for ($i = 0; $i < $countToday && $created < 1000; $i++) {
				$room = $rooms->random();
				$currency = $currencies->random();
				$buyin = fake()->randomElement($buyins);
				$bountyCount = fake()->numberBetween(0, 4);
				$rebuyCount = fake()->numberBetween(0, 3);
				$doubleRebuy = fake()->boolean(15);
				$playersCount = fake()->numberBetween(30, 5000);

				$place = null;
				$cashout = null;
				$cashoutBounty = null;
				$itmChance = fake()->numberBetween(1, 100);
				if ($itmChance <= 18) {
					$place = fake()->numberBetween(1, (int) ($playersCount * 0.15));
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
					$totalBuyin = $buyin + ($bountyCount * $buyin) + ($rebuyCount * $buyin) + ($doubleRebuy ? 2 * $buyin : 0);
					$cashout = round($totalBuyin * $prizeMultiplier, 2);
					if ($bountyCount > 0 && fake()->boolean(40)) {
						$cashoutBounty = round($buyin * fake()->numberBetween(1, $bountyCount * 2), 2);
					}
				} else {
					$place = fake()->numberBetween((int) ($playersCount * 0.15) + 1, $playersCount);
				}

				Tournament::create([
					'user_id' => $testUser->id,
					'room_id' => $room->id,
					'currency_id' => $currency->id,
					'buyin' => $buyin,
					'date' => $currentDate->format('Y-m-d'),
					'place' => $place,
					'cashout' => $cashout,
					'cashout_bounty' => $cashoutBounty,
					'bounty_count' => $bountyCount,
					'rebuy_count' => $rebuyCount,
					'double_rebuy' => $doubleRebuy,
					'players_count' => $playersCount,
				]);
				$created++;
			}
			$currentDate->addDay();
		}
	}
}
