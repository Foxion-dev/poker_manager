<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
	public function run(): void
	{
		$currencies = [
			['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'rate_to_usd' => 1.0000],
			['name' => 'Russian Ruble', 'code' => 'RUB', 'symbol' => '₽', 'rate_to_usd' => 75.5000],
			['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'rate_to_usd' => 0.9624],
			['name' => 'Ukrainian Hryvnia', 'code' => 'UAH', 'symbol' => '₴', 'rate_to_usd' => 41.6700],
			['name' => 'Chinese Yuan', 'code' => 'CNY', 'symbol' => '¥', 'rate_to_usd' => 7.3100],
		];

		foreach ($currencies as $currency) {
			Currency::firstOrCreate(
				['code' => $currency['code']],
				$currency
			);
		}
	}
}
