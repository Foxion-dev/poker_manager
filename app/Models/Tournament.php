<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tournament extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'room_id',
		'buyin',
		'date',
		'place',
		'cashout',
		'bounty_count',
		'players_count',
	];

	protected function casts(): array
	{
		return [
			'date' => 'date',
			'buyin' => 'decimal:2',
			'cashout' => 'decimal:2',
			'place' => 'integer',
			'bounty_count' => 'integer',
			'players_count' => 'integer',
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

	public function getProfitAttribute(): float
	{
		$totalBuyin = $this->buyin + ($this->bounty_count * $this->buyin);
		return ($this->cashout ?? 0) - $totalBuyin;
	}
}
