<?php

namespace App\Services;

class MoneyService
{
	public static function toUsd(float $amount, ?float $rateToUsd): float
	{
		if ($rateToUsd === null || $rateToUsd == 0 || $rateToUsd == 1) {
			return $amount;
		}
		return $amount / $rateToUsd;
	}

	public static function toUsdSqlExpression(string $amountExpression, string $rateColumn): string
	{
		return "CASE WHEN {$rateColumn} IS NULL OR {$rateColumn} = 0 OR {$rateColumn} = 1 THEN ({$amountExpression}) ELSE ({$amountExpression}) / {$rateColumn} END";
	}
}
