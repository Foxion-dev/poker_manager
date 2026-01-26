<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePackRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'max:255'],
			'start_date' => ['required', 'date'],
			'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
			'buyin' => ['required', 'numeric', 'min:0'],
			'cashout' => ['nullable', 'numeric', 'min:0'],
			'currency_id' => ['nullable', 'exists:currencies,id'],
			'description' => ['nullable', 'string'],
		];
	}
}
