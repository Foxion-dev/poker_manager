<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
			'icon' => ['nullable', 'string', 'max:10'],
			'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
			'currency_id' => ['nullable', 'exists:currencies,id'],
			'currency_ids' => ['nullable', 'array'],
			'currency_ids.*' => ['exists:currencies,id'],
		];
	}

	public function withValidator($validator): void
	{
		$validator->after(function ($validator) {
			$currencyId = $this->input('currency_id');
			$currencyIds = $this->input('currency_ids', []);

			if ($currencyId && !in_array($currencyId, $currencyIds)) {
				$validator->errors()->add('currency_id', 'Валюта по умолчанию должна быть в списке доступных валют');
			}
		});
	}
}
