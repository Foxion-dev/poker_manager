<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
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
			'description' => ['nullable', 'string'],
			'is_public' => ['sometimes', 'boolean'],
			'password' => ['nullable', 'string', 'min:4', 'required_if:is_public,true'],
		];
	}
}
