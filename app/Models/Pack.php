<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pack extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'name',
		'start_date',
		'end_date',
		'buyin',
		'cashout',
		'currency_id',
		'description',
	];

	protected function casts(): array
	{
		return [
			'start_date' => 'date',
			'end_date' => 'date',
			'buyin' => 'decimal:2',
			'cashout' => 'decimal:2',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function currency(): BelongsTo
	{
		return $this->belongsTo(Currency::class);
	}

	public function getBuyinUsdAttribute(): float
	{
		if (!$this->currency || !$this->currency->rate_to_usd || $this->currency->rate_to_usd == 0 || $this->currency->rate_to_usd == 1) {
			return (float) $this->buyin;
		}
		return round((float) $this->buyin / $this->currency->rate_to_usd, 2);
	}

	public function getCashoutUsdAttribute(): float
	{
		if (!$this->cashout) {
			return 0;
		}
		if (!$this->currency || !$this->currency->rate_to_usd || $this->currency->rate_to_usd == 0 || $this->currency->rate_to_usd == 1) {
			return (float) $this->cashout;
		}
		return round((float) $this->cashout / $this->currency->rate_to_usd, 2);
	}

	public function getProfitUsdAttribute(): float
	{
		return round($this->cashout_usd - $this->buyin_usd, 2);
	}

	public function getRoiAttribute(): float
	{
		if ($this->buyin_usd == 0) {
			return 0;
		}
		$roi = (($this->cashout_usd - $this->buyin_usd) / $this->buyin_usd) * 100;
		return round($roi, 2);
	}

	public function getIsItmAttribute(): bool
	{
		return $this->cashout !== null;
	}

	public static function getBuyinUsdExpression(): string
	{
		return "
			CASE 
				WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
				THEN packs.buyin
				ELSE packs.buyin / currencies.rate_to_usd
			END
		";
	}

	public static function getCashoutUsdExpression(): string
	{
		return "
			CASE 
				WHEN packs.cashout IS NULL THEN 0
				WHEN currencies.rate_to_usd IS NULL OR currencies.rate_to_usd = 0 OR currencies.rate_to_usd = 1 
				THEN packs.cashout
				ELSE packs.cashout / currencies.rate_to_usd
			END
		";
	}

	public static function getProfitUsdExpression(): string
	{
		return self::getCashoutUsdExpression() . ' - ' . self::getBuyinUsdExpression();
	}
}
