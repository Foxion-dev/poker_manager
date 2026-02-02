<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramBotSetting extends Model
{
	protected $fillable = [
		'bot_token',
		'is_enabled',
	];

	protected function casts(): array
	{
		return [
			'bot_token' => 'encrypted',
			'is_enabled' => 'boolean',
		];
	}

	public static function instance(): self
	{
		$row = self::first();
		if ($row) {
			return $row;
		}
		return self::create(['is_enabled' => false]);
	}
}
