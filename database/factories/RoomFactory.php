<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
	public function definition(): array
	{
		$rooms = [
			['name' => 'PokerStars', 'icon' => '🎰'],
			['name' => '888poker', 'icon' => '🃏'],
			['name' => 'partypoker', 'icon' => '🎲'],
			['name' => 'GGPoker', 'icon' => '♠️'],
			['name' => 'Winamax', 'icon' => '♥️'],
			['name' => 'Unibet', 'icon' => '♦️'],
			['name' => 'Bet365', 'icon' => '♣️'],
			['name' => 'ACR', 'icon' => '🎴'],
		];

		$room = fake()->randomElement($rooms);

		return [
			'name' => $room['name'],
			'icon' => $room['icon'],
		];
	}
}
