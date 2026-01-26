<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationTournament extends Model
{
	use HasFactory;

	protected $fillable = [
		'location_id',
		'name',
		'buyin',
		'currency_id',
		'format',
		'date',
		'is_finished',
	];

	protected function casts(): array
	{
		return [
			'buyin' => 'decimal:2',
			'date' => 'date',
			'is_finished' => 'boolean',
		];
	}

	public function location(): BelongsTo
	{
		return $this->belongsTo(Location::class);
	}

	public function participants(): HasMany
	{
		return $this->hasMany(LocationTournamentParticipant::class);
	}

	public function currency(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Currency::class);
	}

	public function getFormatLabelAttribute(): string
	{
		return match ($this->format) {
			'classic' => 'Классик',
			'classic_bounty' => 'Классик баунти',
			'progressive_bounty' => 'Прогрессив баунти',
			default => $this->format,
		};
	}
}
