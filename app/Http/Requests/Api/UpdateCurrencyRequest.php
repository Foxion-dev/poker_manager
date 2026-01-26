<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'name' => ['sometimes', 'required', 'string', 'max:255'],
			'code' => ['sometimes', 'required', 'string', 'size:3', Rule::unique('currencies', 'code')->ignore($this->route('currency'))],
			'symbol' => ['nullable', 'string', 'max:5'],
			'rate_to_usd' => ['sometimes', 'required', 'numeric', 'min:0'],
		];
	}
}
