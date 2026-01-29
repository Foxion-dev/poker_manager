<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
			'currency_id',
			'date',
			'place',
			'cashout',
			'cashout_bounty',
			'bounty_count',
			'rebuy_count',
			'double_rebuy',
			'players_count',
	];

	protected function casts(): array
	{
		return [
			'date' => 'date',
			'buyin' => 'decimal:2',
			'cashout' => 'decimal:2',
			'cashout_bounty' => 'decimal:2',
			'place' => 'integer',
			'bounty_count' => 'integer',
			'rebuy_count' => 'integer',
			'double_rebuy' => 'boolean',
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

	public function currency(): BelongsTo
	{
		return $this->belongsTo(Currency::class);
	}

	public function getProfitAttribute(): float
	{
		$totalBuyin = $this->buyin + ($this->bounty_count * $this->buyin);
		$rebuyAmount = ($this->rebuy_count ?? 0) * $this->buyin;
		if ($this->double_rebuy ?? false) {
			$rebuyAmount += 2 * $this->buyin;
		}
		$totalBuyin += $rebuyAmount;
			$totalCashout = ($this->cashout ?? 0) + ($this->cashout_bounty ?? 0);
			return $totalCashout - $totalBuyin;
	}

	public function scopeWithUsd(Builder $query): Builder
	{
		return $query->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->select('tournaments.*')
			->selectRaw('
				CASE 
					WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
					THEN tournaments.buyin
					ELSE tournaments.buyin / currencies.rate_to_usd
				END as buyin_usd
			')
			->selectRaw('
				CASE 
					WHEN tournaments.cashout IS NULL AND tournaments.cashout_bounty IS NULL THEN 0
					WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
					THEN COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)
					ELSE (COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)) / currencies.rate_to_usd
				END as cashout_usd
			')
			->selectRaw('
				CASE 
					WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
					THEN tournaments.buyin + (COALESCE(tournaments.bounty_count, 0) * tournaments.buyin) + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)
					ELSE (tournaments.buyin + (COALESCE(tournaments.bounty_count, 0) * tournaments.buyin) + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)) / currencies.rate_to_usd
				END as total_buyin_usd
			')
			->selectRaw('
				CASE 
					WHEN tournaments.cashout IS NULL AND tournaments.cashout_bounty IS NULL THEN 0
					WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
					THEN COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)
					ELSE (COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)) / currencies.rate_to_usd
				END
				-
				CASE 
					WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
					THEN tournaments.buyin + (COALESCE(tournaments.bounty_count, 0) * tournaments.buyin) + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)
					ELSE (tournaments.buyin + (COALESCE(tournaments.bounty_count, 0) * tournaments.buyin) + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)) / currencies.rate_to_usd
				END as profit_usd
			');
	}

	public static function getBuyinUsdExpression(): string
	{
		return "
			CASE 
				WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
				THEN tournaments.buyin + (COALESCE(tournaments.bounty_count, 0) * tournaments.buyin) + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)
				ELSE (tournaments.buyin + (COALESCE(tournaments.bounty_count, 0) * tournaments.buyin) + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)) / currencies.rate_to_usd
			END
		";
	}

	public static function getCashoutUsdExpression(): string
	{
		return "
			CASE 
				WHEN tournaments.cashout IS NULL AND tournaments.cashout_bounty IS NULL THEN 0
				WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
				THEN COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)
				ELSE (COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)) / currencies.rate_to_usd
			END
		";
	}

	public static function getProfitUsdExpression(): string
	{
		return self::getCashoutUsdExpression() . ' - ' . self::getBuyinUsdExpression();
	}
}
