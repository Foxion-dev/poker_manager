<?php

use App\Services\TelegramBotService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
	$this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:webhook-info', function () {
	$service = app(TelegramBotService::class);
	if ($service->getToken() === null) {
		$this->error('Бот не настроен: токен не задан или бот выключен в админке.');
		return;
	}
	$info = $service->getWebhookInfo();
	if (!$info) {
		$this->error('Не удалось получить данные от Telegram API.');
		return;
	}
	$url = $info['result']['url'] ?? '';
	if ($url === '') {
		$this->warn('Вебхук не установлен. Сохраните настройки Telegram в админке (Настройки → Telegram) или установите вручную.');
		$this->line('Ожидаемый URL: ' . rtrim(config('app.url'), '/') . '/api/telegram/webhook');
	} else {
		$this->info('Текущий вебхук: ' . $url);
	}
})->purpose('Проверить, установлен ли вебхук для Telegram-бота');
