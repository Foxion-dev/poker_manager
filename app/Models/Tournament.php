<?php

namespace App\Models;

use App\Services\MoneyService;
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
			'date' => 'datetime',
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
		$rebuyAmount = ($this->rebuy_count ?? 0) * $this->buyin;
		if ($this->double_rebuy ?? false) {
			$rebuyAmount += 2 * $this->buyin;
		}
		$totalBuyin = $this->buyin + $rebuyAmount;
		$totalCashout = ($this->cashout ?? 0) + ($this->cashout_bounty ?? 0);
		return $totalCashout - $totalBuyin;
	}

	public function scopeWithUsd(Builder $query): Builder
	{
		$buyinUsd = MoneyService::toUsdSqlExpression('tournaments.buyin', 'currencies.rate_to_usd');
		$totalBuyinExpr = 'tournaments.buyin + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)';
		$totalBuyinUsd = MoneyService::toUsdSqlExpression($totalBuyinExpr, 'currencies.rate_to_usd');
		$totalCashoutExpr = 'COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)';
		$totalCashoutUsd = MoneyService::toUsdSqlExpression($totalCashoutExpr, 'currencies.rate_to_usd');
		$cashoutUsd = "CASE WHEN tournaments.cashout IS NULL AND tournaments.cashout_bounty IS NULL THEN 0 ELSE {$totalCashoutUsd} END";

		return $query->leftJoin('currencies', 'tournaments.currency_id', '=', 'currencies.id')
			->select('tournaments.*')
			->selectRaw("{$buyinUsd} as buyin_usd")
			->selectRaw("{$cashoutUsd} as cashout_usd")
			->selectRaw("{$totalBuyinUsd} as total_buyin_usd")
			->selectRaw("{$cashoutUsd} - {$totalBuyinUsd} as profit_usd");
	}

	public static function getBuyinUsdExpression(): string
	{
		$expr = 'tournaments.buyin + (COALESCE(tournaments.rebuy_count, 0) * tournaments.buyin) + (CASE WHEN tournaments.double_rebuy = 1 THEN 2 * tournaments.buyin ELSE 0 END)';
		return MoneyService::toUsdSqlExpression($expr, 'currencies.rate_to_usd');
	}

	public static function getCashoutUsdExpression(): string
	{
		$totalCashoutExpr = 'COALESCE(tournaments.cashout, 0) + COALESCE(tournaments.cashout_bounty, 0)';
		$totalCashoutUsd = MoneyService::toUsdSqlExpression($totalCashoutExpr, 'currencies.rate_to_usd');
		return "CASE WHEN tournaments.cashout IS NULL AND tournaments.cashout_bounty IS NULL THEN 0 ELSE {$totalCashoutUsd} END";
	}

	public static function getProfitUsdExpression(): string
	{
		return self::getCashoutUsdExpression() . ' - ' . self::getBuyinUsdExpression();
	}
}
