<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateTelegramBotSettingsRequest;
use App\Models\TelegramBotSetting;
use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;

class AdminTelegramSettingsController extends Controller
{
	public function __construct(
		private TelegramBotService $telegramBot
	) {
	}

	public function show(): JsonResponse
	{
		$settings = TelegramBotSetting::instance();
		return response()->json([
			'has_token' => !empty($settings->bot_token),
			'is_enabled' => $settings->is_enabled,
		]);
	}

	public function update(UpdateTelegramBotSettingsRequest $request): JsonResponse
	{
		$settings = TelegramBotSetting::instance();
		$data = $request->only('is_enabled');
		if ($request->has('bot_token')) {
			$data['bot_token'] = $request->input('bot_token') ?: null;
		}
		$settings->update($data);
		if ($settings->is_enabled && !empty($settings->bot_token)) {
			$webhookUrl = rtrim(config('app.url'), '/') . '/api/telegram/webhook';
			$this->telegramBot->setWebhook($webhookUrl);
		}
		return response()->json([
			'has_token' => !empty($settings->bot_token),
			'is_enabled' => $settings->is_enabled,
		]);
	}
}
