<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'room_id' => ['required', 'exists:rooms,id'],
			'buyin' => ['required', 'numeric', 'min:0'],
			'date' => ['required', 'date'],
			'place' => ['nullable', 'integer', 'min:1'],
			'cashout' => ['nullable', 'numeric', 'min:0'],
			'bounty_count' => ['nullable', 'integer', 'min:0'],
			'players_count' => ['nullable', 'integer', 'min:2'],
		];
	}
}
