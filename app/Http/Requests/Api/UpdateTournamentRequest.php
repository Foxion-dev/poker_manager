<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentRequest extends FormRequest
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
			'room_id' => ['sometimes', 'required', 'exists:rooms,id'],
			'buyin' => ['sometimes', 'required', 'numeric', 'min:0'],
			'currency_id' => ['nullable', 'exists:currencies,id'],
			'date' => ['sometimes', 'required', 'date'],
			'place' => ['nullable', 'integer', 'min:1'],
			'cashout' => ['nullable', 'numeric', 'min:0'],
			'bounty_count' => ['nullable', 'integer', 'min:0'],
			'rebuy_count' => ['nullable', 'integer', 'min:0'],
			'double_rebuy' => ['nullable', 'boolean'],
			'players_count' => ['nullable', 'integer', 'min:2'],
		];
	}
}
