<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\LocationUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AspenLocationSeeder extends Seeder
{
	public function run(): void
	{
		$owner = User::firstOrCreate(
			['email' => 'mamaama141996@gmail.com'],
			[
				'name' => 'Богдан',
				'password' => Hash::make('password'),
				'balance' => 0,
				'is_admin' => true,
			]
		);

		$location = Location::firstOrCreate(
			['name' => 'Аспен'],
			[
				'user_id' => $owner->id,
				'name' => 'Аспен',
				'description' => null,
				'is_public' => true,
				'password' => Hash::make('poker123'),
			]
		);

		if (!$location->admins()->where('user_id', $owner->id)->exists()) {
			$location->admins()->attach($owner->id);
		}

		$participants = [
			'Богдан',
			'Аня',
			'Саша Мартов',
			'Саша Гришин',
			'Саша Дробовик',
			'Кристина Дробовик',
			'Самвэл',
			'Родион',
			'Максим Саньков',
			'Максим в очках',
			'Артём',
			'Катя',
			'Сергей',
			'Роман Дзёмин',
			'Рома Итальянец',
			'Алехандро',
			'Ваня Спайдер',
			'Вася',
			'Даниил Сальников',
			'Даниил Лапичев',
			'Никита Семёнов',
			'Андрей Белаш',
			'Игорь Б',
			'Игорь Гитарист',
			'Дима Аспен',
			'Дима Пупупу',
			'Валера Гуков',
			'Миша',
			'Диана',
			'Дима РДВ',
			'Виталик',
			'Настя',
			'Азат',
			'Кирилл',
			'Денис',
			'Дима Гладков',
			'Женя Аспен',
		];

		foreach ($participants as $participantName) {
			LocationUser::firstOrCreate(
				[
					'location_id' => $location->id,
					'name' => $participantName,
					'user_id' => null,
				]
			);
		}
	}
}
