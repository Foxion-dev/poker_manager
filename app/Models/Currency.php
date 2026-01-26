<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'code',
		'symbol',
		'rate_to_usd',
	];

	protected function casts(): array
	{
		return [
			'rate_to_usd' => 'decimal:4',
		];
	}

	public function tournaments(): HasMany
	{
		return $this->hasMany(Tournament::class);
	}
}
