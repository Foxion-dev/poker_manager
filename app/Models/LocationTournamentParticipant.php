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
		'user_id',
		'place',
		'prize',
	];

	protected function casts(): array
	{
		return [
			'place' => 'integer',
			'prize' => 'decimal:2',
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
}
