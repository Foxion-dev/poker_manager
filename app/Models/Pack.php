<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pack extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'name',
		'start_date',
		'end_date',
		'description',
	];

	protected function casts(): array
	{
		return [
			'start_date' => 'date',
			'end_date' => 'date',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function tournaments(): HasMany
	{
		return $this->hasMany(Tournament::class);
	}

	public function getTotalTournamentsAttribute(): int
	{
		return $this->tournaments()->count();
	}

	public function getTotalProfitUsdAttribute(): float
	{
		$profit = $this->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->selectRaw('SUM(' . Tournament::getProfitUsdExpression() . ') as profit')
			->value('profit');

		return round($profit ?? 0, 2);
	}

	public function getTotalBuyinUsdAttribute(): float
	{
		$buyin = $this->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->selectRaw('SUM(' . Tournament::getBuyinUsdExpression() . ') as total_buyin')
			->value('total_buyin');

		return round($buyin ?? 0, 2);
	}

	public function getTotalCashoutUsdAttribute(): float
	{
		$cashout = $this->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->selectRaw('SUM(' . Tournament::getCashoutUsdExpression() . ') as total_cashout')
			->value('total_cashout');

		return round($cashout ?? 0, 2);
	}

	public function getRoiAttribute(): float
	{
		$totalBuyin = $this->total_buyin_usd;
		if ($totalBuyin == 0) {
			return 0;
		}

		$roi = (($this->total_cashout_usd - $totalBuyin) / $totalBuyin) * 100;
		return round($roi, 2);
	}

	public function getItmCountAttribute(): int
	{
		return $this->tournaments()->whereNotNull('cashout')->count();
	}

	public function getItmPercentageAttribute(): float
	{
		$total = $this->total_tournaments;
		if ($total == 0) {
			return 0;
		}

		return round(($this->itm_count / $total) * 100, 2);
	}

	public function getAverageBuyinUsdAttribute(): float
	{
		$avgBuyin = $this->tournaments()
			->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->selectRaw('AVG(' . Tournament::getBuyinUsdExpression() . ') as avg_buyin')
			->value('avg_buyin');

		return round($avgBuyin ?? 0, 2);
	}
}
