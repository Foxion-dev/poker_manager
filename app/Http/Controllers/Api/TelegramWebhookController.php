<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
	public function __construct(
		private TelegramBotService $telegramBot
	) {
	}

	public function __invoke(Request $request): Response
	{
		if ($this->telegramBot->getToken() === null) {
			return response()->noContent(200);
		}
		$payload = $request->all();
		if (empty($payload) && $request->getContent()) {
			$decoded = json_decode($request->getContent(), true);
			if (is_array($decoded)) {
				$payload = $decoded;
			}
		}
		if (empty($payload)) {
			return response()->noContent(200);
		}
		try {
			$this->telegramBot->handleUpdate($payload);
		} catch (\Throwable $e) {
			Log::error('Telegram webhook error', [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'payload' => $payload,
			]);
		}
		return response()->noContent(200);
	}
}
