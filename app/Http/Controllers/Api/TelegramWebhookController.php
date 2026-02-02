<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
		if (empty($payload)) {
			return response()->noContent(200);
		}
		$this->telegramBot->handleUpdate($payload);
		return response()->noContent(200);
	}
}
