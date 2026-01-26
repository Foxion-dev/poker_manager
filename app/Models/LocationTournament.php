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
		'rake',
		'rake_type',
		'prize_distribution',
		'date',
		'is_finished',
	];

	protected function casts(): array
	{
		return [
			'buyin' => 'decimal:2',
			'itm_percentage' => 'decimal:2',
			'rake' => 'decimal:2',
			'prize_distribution' => 'array',
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
		$itmPercentage = (float) ($this->itm_percentage ?? 15);
		$rakeType = $this->rake_type ?? 'fixed';
		$rake = (float) ($this->rake ?? 30);
		
		$prizePoolBeforeRake = $totalBuyin * ($itmPercentage / 100);
		
		if ($rakeType === 'percentage') {
			$rakeAmount = $prizePoolBeforeRake * ($rake / 100);
		} else {
			$rakeAmount = $rake;
		}
		
		return round($prizePoolBeforeRake - $rakeAmount, 2);
	}

	public function getPrizeDistributionAttribute(): array
	{
		if ($this->prize_distribution && is_array($this->prize_distribution) && count($this->prize_distribution) > 0) {
			$prizePool = $this->prize_pool;
			return array_map(function($prize) use ($prizePool) {
				return [
					'place' => $prize['place'],
					'percentage' => $prize['percentage'],
					'prize' => round($prizePool * ($prize['percentage'] / 100), 2),
				];
			}, $this->prize_distribution);
		}

		$prizePool = $this->prize_pool;
		
		if ($prizePool <= 0) {
			return [];
		}

		$participantsCount = $this->participants->count();
		if ($participantsCount === 0) {
			return [];
		}

		$itmPercentage = (float) ($this->itm_percentage ?? 15);
		$itmPlacesFloat = $participantsCount * ($itmPercentage / 100);
		
		if ($itmPlacesFloat < 0.5) {
			return [];
		}
		
		$itmPlaces = max(1, min((int) ceil($itmPlacesFloat), $participantsCount));
		
		if ($itmPlaces === 0 || $itmPlaces > $participantsCount) {
			return [];
		}

		$prizes = [];
		$totalPercentage = 0;
		
		for ($place = 1; $place <= $itmPlaces; $place++) {
			$percentage = $this->calculatePrizePercentage($place, $itmPlaces);
			$totalPercentage += $percentage;
			$prize = round($prizePool * ($percentage / 100), 2);
			$prize = round($prize / 5) * 5;
			
			$prizes[] = [
				'place' => $place,
				'percentage' => $percentage,
				'prize' => $prize,
			];
		}

		if ($totalPercentage < 100 && count($prizes) > 0) {
			$diff = 100 - $totalPercentage;
			$prizes[0]['percentage'] += $diff;
			$prizes[0]['prize'] = round($prizePool * ($prizes[0]['percentage'] / 100), 2);
			$prizes[0]['prize'] = round($prizes[0]['prize'] / 5) * 5;
		}

		return $prizes;
	}

	private function calculatePrizePercentage(int $place, int $totalPlaces): float
	{
		if ($totalPlaces === 1) {
			return 100.0;
		}

		if ($totalPlaces === 2) {
			return $place === 1 ? 60.0 : 40.0;
		}

		if ($totalPlaces === 3) {
			return match($place) {
				1 => 60.0,
				2 => 30.0,
				3 => 10.0,
				default => 0.0,
			};
		}

		$basePercentages = [
			1 => 50.0,
			2 => 25.0,
			3 => 12.5,
		];

		if ($place <= 3) {
			$percentage = $basePercentages[$place] ?? 0;
		} else {
			$remainingPlaces = $totalPlaces - 3;
			$remainingPercentage = 12.5;
			$percentage = $remainingPercentage / $remainingPlaces;
		}

		return round($percentage, 2);
	}
}
