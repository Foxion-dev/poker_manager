<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationTournamentRequest extends FormRequest
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
			'name' => ['sometimes', 'required', 'string', 'max:255'],
			'buyin' => ['sometimes', 'required', 'numeric', 'min:0'],
			'currency_id' => ['nullable', 'exists:currencies,id'],
			'format' => ['sometimes', 'required', 'in:classic,classic_bounty,progressive_bounty'],
			'date' => ['sometimes', 'required', 'date'],
			'participants' => ['sometimes', 'array', 'min:1'],
			'participants.*.name' => ['required_without:participants.*.user_id', 'nullable', 'string', 'max:255'],
			'participants.*.user_id' => ['nullable', 'exists:users,id'],
			'participants.*.place' => ['required', 'integer', 'min:1'],
			'participants.*.prize' => ['nullable', 'numeric', 'min:0'],
		];
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$participants = $this->input('participants', []);
			if (empty($participants)) {
				return;
			}

			$places = array_column($participants, 'place');

			$userIds = [];
			foreach ($participants as $index => $participant) {
				if (empty($participant['user_id']) && empty($participant['name'])) {
					$validator->errors()->add("participants.{$index}", 'Необходимо указать либо пользователя, либо имя участника.');
				}

				if (!empty($participant['user_id'])) {
					$userIds[] = $participant['user_id'];
				}
			}

			if (count($userIds) !== count(array_unique($userIds))) {
				$validator->errors()->add('participants', 'Каждый пользователь может быть добавлен только один раз.');
			}

			if (count($places) !== count(array_unique($places))) {
				$validator->errors()->add('participants', 'Места участников должны быть уникальными.');
			}
		});
	}
}
