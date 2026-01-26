<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class LocationUserPassword extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'location_id',
		'password',
	];

	protected $hidden = [
		'password',
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function location(): BelongsTo
	{
		return $this->belongsTo(Location::class);
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
			return false;
		}
		return Hash::check($password, $this->password);
	}
}
