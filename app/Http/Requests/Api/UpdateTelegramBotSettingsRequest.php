<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTelegramBotSettingsRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'bot_token' => ['nullable', 'string', 'max:255'],
			'is_enabled' => ['sometimes', 'boolean'],
		];
	}
}
