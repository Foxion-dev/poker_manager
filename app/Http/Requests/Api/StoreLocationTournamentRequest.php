<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationTournamentRequest extends FormRequest
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
			'name' => ['required', 'string', 'max:255'],
			'buyin' => ['required', 'numeric', 'min:0'],
			'format' => ['required', 'in:classic,classic_bounty,progressive_bounty'],
			'date' => ['required', 'date'],
			'participants' => ['required', 'array', 'min:1'],
			'participants.*.user_id' => ['required', 'exists:users,id'],
			'participants.*.place' => ['required', 'integer', 'min:1'],
			'participants.*.prize' => ['nullable', 'numeric', 'min:0'],
		];
	}
}
