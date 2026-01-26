<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoom extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'room_id',
		'balance',
	];

	protected function casts(): array
	{
		return [
			'balance' => 'decimal:2',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function room(): BelongsTo
	{
		return $this->belongsTo(Room::class);
	}
}
