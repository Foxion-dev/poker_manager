<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'icon',
	];

	public function users(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'user_rooms')
			->withPivot('balance')
			->withTimestamps();
	}

	public function tournaments(): HasMany
	{
		return $this->hasMany(Tournament::class);
	}

	public function userRooms(): HasMany
	{
		return $this->hasMany(UserRoom::class);
	}
}
