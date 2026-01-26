<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TournamentFactory extends Factory
{
	public function definition(): array
	{
		$buyin = fake()->randomElement([5.50, 11, 22, 33, 55, 109, 215, 530]);
		$place = fake()->numberBetween(1, 1000);
		$bountyCount = fake()->numberBetween(0, 5);
		$totalBuyin = $buyin + ($bountyCount * $buyin);

		$cashout = null;
		if ($place <= 100) {
			$prizeMultiplier = fake()->randomFloat(2, 1.5, 10);
			$cashout = $totalBuyin * $prizeMultiplier;
		}

		return [
			'user_id' => User::factory(),
			'room_id' => Room::factory(),
			'buyin' => $buyin,
			'date' => fake()->dateTimeBetween('-6 months', 'now'),
			'place' => $place,
			'cashout' => $cashout,
			'bounty_count' => $bountyCount,
		];
	}
}
