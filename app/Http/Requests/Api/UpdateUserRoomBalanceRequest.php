<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoomBalanceRequest extends FormRequest
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
			'balance' => ['required', 'numeric', 'min:0'],
		];
	}
}
