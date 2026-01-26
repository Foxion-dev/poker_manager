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
			'participants.*.user_id' => ['required', 'exists:users,id', 'distinct'],
			'participants.*.place' => ['required', 'integer', 'min:1'],
			'participants.*.prize' => ['nullable', 'numeric', 'min:0'],
		];
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$participants = $this->input('participants', []);
			$userIds = array_column($participants, 'user_id');
			$places = array_column($participants, 'place');

			if (count($userIds) !== count(array_unique($userIds))) {
				$validator->errors()->add('participants', 'Каждый участник может быть добавлен только один раз.');
			}

			if (count($places) !== count(array_unique($places))) {
				$validator->errors()->add('participants', 'Места участников должны быть уникальными.');
			}
		});
	}
}
