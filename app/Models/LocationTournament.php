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
		$rakeType = $this->rake_type ?? 'fixed';
		$rake = (float) ($this->rake ?? 30);
		
		if ($rakeType === 'percentage') {
			$rakeAmount = $totalBuyin * ($rake / 100);
		} else {
			$rakeAmount = $rake;
		}
		
		return round($totalBuyin - $rakeAmount, 2);
	}

	public function getPrizeDistributionAttribute(): array
	{
		$rawDistribution = $this->attributes['prize_distribution'] ?? null;
		if ($rawDistribution !== null && $rawDistribution !== '') {
			$decoded = is_string($rawDistribution) ? json_decode($rawDistribution, true) : $rawDistribution;
			if (is_array($decoded) && count($decoded) > 0 && isset($decoded[0]['percentage'])) {
				$prizePool = $this->prize_pool;
				return array_map(function($prize) use ($prizePool) {
					$prizeAmount = round($prizePool * ($prize['percentage'] / 100), 2);
					$prizeAmount = round($prizeAmount / 5) * 5;
					return [
						'place' => $prize['place'],
						'percentage' => $prize['percentage'],
						'prize' => $prizeAmount,
					];
				}, $decoded);
			}
		}

		$prizePool = $this->prize_pool;
		
		if ($prizePool <= 0) {
			return [];
		}

		$participantsCount = $this->participants->count();
		if ($participantsCount === 0) {
			return [];
		}

		$totalRebuys = $this->participants->sum('rebuy');
		$totalEntries = $participantsCount + $totalRebuys;
		
		if ($totalEntries === 0) {
			return [];
		}

		$itmPercentage = (float) ($this->itm_percentage ?? 15);
		$itmPlacesFloat = $totalEntries * ($itmPercentage / 100);
		
		if ($itmPlacesFloat < 0.5) {
			return [];
		}
		
		$itmPlaces = max(1, min((int) round($itmPlacesFloat), $totalEntries));
		
		if ($itmPlaces === 0 || $itmPlaces > $totalEntries) {
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
		$distributions = [
			1 => [100],
			2 => [70, 30],
			3 => [60, 30, 10],
			4 => [50, 25, 15, 10],
			5 => [45, 25, 15, 10, 5],
			6 => [40, 23, 15, 10, 7, 5],
			7 => [37, 22, 14, 10, 7, 5, 5],
			8 => [35, 21, 14, 10, 7, 5, 4, 4],
			9 => [33, 20, 14, 10, 7, 5, 4, 4, 3],
			10 => [31, 20, 13, 10, 7, 5, 4, 4, 3, 3],
		];

		if ($totalPlaces <= 10 && isset($distributions[$totalPlaces])) {
			return (float) ($distributions[$totalPlaces][$place - 1] ?? 0);
		}

		if ($totalPlaces > 10) {
			if ($place === 1) {
				return 31.0;
			} elseif ($place === 2) {
				return 20.0;
			} elseif ($place === 3) {
				return 13.0;
			} elseif ($place <= 6) {
				$percentages = [10, 7, 5];
				return (float) ($percentages[$place - 4] ?? 0);
			} else {
				$remainingPlaces = $totalPlaces - 6;
				$remainingPercentage = 100 - 31 - 20 - 13 - 10 - 7 - 5;
				return round($remainingPercentage / $remainingPlaces, 2);
			}
		}

		return 0.0;
	}
}
