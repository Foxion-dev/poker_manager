<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
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
			'description' => ['nullable', 'string'],
			'is_public' => ['sometimes', 'boolean'],
			'password' => ['nullable', 'string', 'min:4'],
		];
	}
}
