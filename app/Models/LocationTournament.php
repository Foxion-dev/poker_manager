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
		'itm_percentage',
		'date',
		'is_finished',
	];

	protected function casts(): array
	{
		return [
			'buyin' => 'decimal:2',
			'itm_percentage' => 'decimal:2',
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

	public function getTotalBuyinAttribute(): float
	{
		$buyin = (float) $this->buyin;
		$totalRebuys = $this->participants->sum('rebuy');
		$totalAddons = $this->participants->where('addon', true)->count();
		return ($buyin * $this->participants->count()) + ($totalRebuys * $buyin) + ($totalAddons * $buyin);
	}

	public function getPrizePoolAttribute(): float
	{
		$totalBuyin = $this->total_buyin;
		$itmPercentage = (float) ($this->itm_percentage ?? 100);
		return round($totalBuyin * ($itmPercentage / 100), 2);
	}

	public function getPrizeDistributionAttribute(): array
	{
		$prizePool = $this->prize_pool;
		$itmParticipants = $this->participants->where('place', '<=', 3)->sortBy('place')->values();
		
		if ($itmParticipants->isEmpty()) {
			return [];
		}

		$distribution = [60, 30, 10];
		$prizes = [];
		
		foreach ($itmParticipants as $index => $participant) {
			if ($index >= 3) {
				break;
			}
			
			$percentage = $distribution[$index] ?? 0;
			$prize = round($prizePool * ($percentage / 100), 2);
			$prize = round($prize / 5) * 5;
			
			$prizes[] = [
				'place' => $participant->place,
				'participant_id' => $participant->id,
				'participant_name' => $participant->display_name,
				'percentage' => $percentage,
				'prize' => $prize,
			];
		}

		return $prizes;
	}
}
