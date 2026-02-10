<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTournamentParticipant extends Model
{
	use HasFactory;

	protected $fillable = [
		'location_tournament_id',
		'name',
		'user_id',
		'place',
		'rebuy',
		'addon',
		'is_paid',
		'prize',
		'bounty_stack',
		'bounty_prize',
	];

	protected function casts(): array
	{
		return [
			'place' => 'integer',
			'rebuy' => 'integer',
			'addon' => 'boolean',
			'is_paid' => 'boolean',
			'prize' => 'decimal:2',
			'bounty_stack' => 'decimal:2',
			'bounty_prize' => 'decimal:2',
		];
	}

	public function tournament(): BelongsTo
	{
		return $this->belongsTo(LocationTournament::class, 'location_tournament_id');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function getDisplayNameAttribute(): string
	{
		return $this->name ?? $this->user?->name ?? 'Неизвестный участник';
	}
}
