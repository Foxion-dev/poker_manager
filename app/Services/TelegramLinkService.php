<?php

namespace App\Services;

use App\Models\TelegramLinkCode;
use App\Models\User;
use Illuminate\Support\Str;

class TelegramLinkService
{
	public const CODE_LENGTH = 6;
	public const CODE_TTL_MINUTES = 10;

	public function createLinkCode(User $user): array
	{
		TelegramLinkCode::where('user_id', $user->id)->delete();
		$code = strtoupper(Str::random(self::CODE_LENGTH));
		$expiresAt = now()->addMinutes(self::CODE_TTL_MINUTES);
		TelegramLinkCode::create([
			'user_id' => $user->id,
			'code' => $code,
			'expires_at' => $expiresAt,
		]);
		return [
			'code' => $code,
			'expires_at' => $expiresAt->toIso8601String(),
		];
	}

	public function linkByCode(string $code, int $telegramChatId, ?string $telegramUsername = null): ?User
	{
		$linkCode = TelegramLinkCode::where('code', strtoupper($code))->first();
		if (!$linkCode || $linkCode->isExpired()) {
			return null;
		}
		$user = $linkCode->user;
		$user->getSettings()->update([
			'telegram_chat_id' => $telegramChatId,
			'telegram_username' => $telegramUsername,
		]);
		$linkCode->delete();
		return $user;
	}
}
