<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
	protected $table = 'user_settings';

	protected $fillable = [
		'user_id',
		'disabled_room_ids',
		'telegram_chat_id',
		'telegram_username',
	];

	protected function casts(): array
	{
		return [
			'disabled_room_ids' => 'array',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function getDisabledRoomIds(): array
	{
		return $this->disabled_room_ids ?? [];
	}

	public function addDisabledRoomId(int $roomId): void
	{
		$ids = $this->getDisabledRoomIds();
		if (!in_array($roomId, $ids, true)) {
			$ids[] = $roomId;
			$this->update(['disabled_room_ids' => $ids]);
		}
	}

	public function removeDisabledRoomId(int $roomId): bool
	{
		$ids = $this->getDisabledRoomIds();
		$key = array_search($roomId, $ids, true);
		if ($key === false) {
			return false;
		}
		unset($ids[$key]);
		$this->update(['disabled_room_ids' => array_values($ids)]);
		return true;
	}
}
