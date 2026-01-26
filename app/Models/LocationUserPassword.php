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
			$this->attributes['password'] = encrypt($value);
		}
	}

	public function getPasswordAttribute($value)
	{
		if ($value) {
			try {
				return decrypt($value);
			} catch (\Exception $e) {
				return null;
			}
		}
		return null;
	}
}
