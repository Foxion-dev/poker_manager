<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonalRoomRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'max:255'],
			'icon' => ['nullable', 'string', 'max:10'],
			'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
			'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
			'currency_ids' => ['nullable', 'array'],
			'currency_ids.*' => ['integer', 'exists:currencies,id'],
		];
	}

	public function withValidator($validator): void
	{
		$validator->after(function ($validator) {
			$currencyId = $this->input('currency_id');
			$currencyIds = $this->input('currency_ids', []);

			if ($currencyId && !in_array((int) $currencyId, array_map('intval', $currencyIds))) {
				$validator->errors()->add('currency_id', 'Валюта по умолчанию должна быть в списке доступных валют');
			}
		});
	}
}
