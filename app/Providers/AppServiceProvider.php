<?php

namespace App\Providers;

use App\Services\Telegram\Handlers\AddTournamentHandler;
use App\Services\Telegram\Handlers\BalanceHandler;
use App\Services\Telegram\Handlers\DefaultHandler;
use App\Services\Telegram\Handlers\GetStatsHandler;
use App\Services\Telegram\Handlers\StartHandler;
use App\Services\Telegram\TelegramDispatcher;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->singleton(TelegramDispatcher::class, function ($app) {
			return new TelegramDispatcher([
				$app->make(StartHandler::class),
				$app->make(BalanceHandler::class),
				$app->make(GetStatsHandler::class),
				$app->make(AddTournamentHandler::class),
				$app->make(DefaultHandler::class),
			]);
		});
	}

	public function boot(): void
	{
	}
}
