<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
	protected $fillable = [
		'name',
		'email',
		'password',
		'balance',
		'banned_at',
		'is_admin',
		'last_login_at',
	];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
	protected function casts(): array
	{
		return [
			'email_verified_at' => 'datetime',
			'banned_at' => 'datetime',
			'last_login_at' => 'datetime',
			'password' => 'hashed',
			'balance' => 'decimal:2',
			'is_admin' => 'boolean',
		];
	}

	public function isBanned(): bool
	{
		return $this->banned_at !== null;
	}

	public function isAdmin(): bool
	{
		return $this->is_admin === true;
	}

	public function rooms(): BelongsToMany
	{
		return $this->belongsToMany(Room::class, 'user_rooms')
			->withPivot('balance')
			->withTimestamps();
	}

	public function tournaments(): HasMany
	{
		return $this->hasMany(Tournament::class);
	}

	public function userRooms(): HasMany
	{
		return $this->hasMany(UserRoom::class);
	}

	public function packs(): HasMany
	{
		return $this->hasMany(Pack::class);
	}

	public function locations(): HasMany
	{
		return $this->hasMany(Location::class);
	}

	public function locationTournamentParticipants(): HasMany
	{
		return $this->hasMany(LocationTournamentParticipant::class);
	}

	public function adminLocations(): BelongsToMany
	{
		return $this->belongsToMany(Location::class, 'location_admins')
			->withTimestamps();
	}

	public function locationPasswords(): HasMany
	{
		return $this->hasMany(LocationUserPassword::class);
	}

	public function disabledRooms(): BelongsToMany
	{
		return $this->belongsToMany(Room::class, 'user_room_settings')
			->withTimestamps();
	}
}
