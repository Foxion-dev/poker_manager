<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'max:255'],
			'code' => ['required', 'string', 'size:3', 'unique:currencies,code'],
			'symbol' => ['nullable', 'string', 'max:5'],
			'rate_to_usd' => ['required', 'numeric', 'min:0'],
		];
	}
}
