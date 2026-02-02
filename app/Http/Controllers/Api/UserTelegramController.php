<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotSetting;
use App\Services\TelegramLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTelegramController extends Controller
{
	public function __construct(
		private TelegramLinkService $telegramLinkService
	) {
	}

	public function index(Request $request): JsonResponse
	{
		$user = $request->user();
		$userSettings = $user->getSettings();
		$botSettings = TelegramBotSetting::instance();
		$botEnabled = $botSettings->is_enabled && !empty($botSettings->bot_token);
		return response()->json([
			'connected' => $user->isTelegramConnected(),
			'telegram_username' => $userSettings->telegram_username,
			'bot_enabled' => $botEnabled,
		]);
	}

	public function storeLinkCode(Request $request): JsonResponse
	{
		$user = $request->user();
		$settings = TelegramBotSetting::instance();
		if (!$settings->is_enabled || empty($settings->bot_token)) {
			return response()->json(['message' => 'Telegram bot is not enabled'], 422);
		}
		$data = $this->telegramLinkService->createLinkCode($user);
		return response()->json($data);
	}

	public function destroy(Request $request): JsonResponse
	{
		$request->user()->getSettings()->update([
			'telegram_chat_id' => null,
			'telegram_username' => null,
		]);
		return response()->json(['connected' => false]);
	}
}
