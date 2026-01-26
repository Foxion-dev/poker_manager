<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Location extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'name',
		'description',
		'is_public',
		'password',
	];

	protected $hidden = [
		'password',
	];

	protected function casts(): array
	{
		return [
			'is_public' => 'boolean',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function tournaments(): HasMany
	{
		return $this->hasMany(LocationTournament::class);
	}

	public function admins(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'location_admins')
			->withTimestamps();
	}

	public function users(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'location_users')
			->withPivot('name')
			->withTimestamps();
	}

	public function locationUsers(): HasMany
	{
		return $this->hasMany(LocationUser::class);
	}

	public function isAdmin(User $user): bool
	{
		return $this->user_id === $user->id || $this->admins()->where('user_id', $user->id)->exists();
	}

	public function canManageAdmins(User $user): bool
	{
		return $this->user_id === $user->id || $this->admins()->where('user_id', $user->id)->exists();
	}

	public function setPasswordAttribute($value)
	{
		if ($value) {
			$this->attributes['password'] = Hash::make($value);
		}
	}

	public function checkPassword($password): bool
	{
		if (!$this->password) {
			return true;
		}
		return Hash::check($password, $this->password);
	}

	public function getTournamentsCountAttribute(): int
	{
		return $this->tournaments()->count();
	}

	public function getAverageBuyinAttribute(): float
	{
		$avgBuyin = $this->tournaments()->avg('buyin');
		return round($avgBuyin ?? 0, 2);
	}

	public function getTopPlayersByWinsAttribute(): array
	{
		return \DB::table('location_tournament_participants')
			->join('location_tournaments', 'location_tournament_participants.location_tournament_id', '=', 'location_tournaments.id')
			->where('location_tournaments.location_id', $this->id)
			->where('location_tournament_participants.place', 1)
			->selectRaw('location_tournament_participants.user_id, COUNT(*) as wins')
			->groupBy('location_tournament_participants.user_id')
			->orderByDesc('wins')
			->limit(10)
			->get()
			->map(function ($item) {
				return [
					'user' => User::find($item->user_id),
					'wins' => $item->wins,
				];
			})
			->filter(fn($item) => $item['user'] !== null)
			->values()
			->toArray();
	}

	public function getTopPlayersByPrizeAttribute(): array
	{
		return \DB::table('location_tournament_participants')
			->join('location_tournaments', 'location_tournament_participants.location_tournament_id', '=', 'location_tournaments.id')
			->where('location_tournaments.location_id', $this->id)
			->selectRaw('location_tournament_participants.user_id, SUM(location_tournament_participants.prize) as total_prize')
			->groupBy('location_tournament_participants.user_id')
			->orderByDesc('total_prize')
			->limit(10)
			->get()
			->map(function ($item) {
				return [
					'user' => User::find($item->user_id),
					'total_prize' => round($item->total_prize ?? 0, 2),
				];
			})
			->filter(fn($item) => $item['user'] !== null)
			->values()
			->toArray();
	}
}
