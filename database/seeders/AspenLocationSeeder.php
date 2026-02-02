<?php

namespace Database\Seeders;

use App\Models\Currency;
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
			['email' => 'mamama141996@gmail.com'],
			[
				'name' => 'mamama141996',
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
				'password' => 'poker123',
			]
		);

		if ($location->wasRecentlyCreated || !$location->checkPassword('poker123')) {
			$location->password = 'poker123';
			$location->save();
		}

		if (!$location->admins()->where('user_id', $owner->id)->exists()) {
			$location->admins()->attach($owner->id);
		}

		$gelCurrency = Currency::where('code', 'GEL')->first();
		if ($gelCurrency && !$location->currencies()->where('currency_id', $gelCurrency->id)->exists()) {
			$location->currencies()->attach($gelCurrency->id);
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
