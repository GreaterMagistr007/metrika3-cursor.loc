<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class TelegramAuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'init_data' => [
                'required',
                'string',
                'min:10',
                'max:4096'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'init_data.required' => 'Данные Telegram обязательны',
            'init_data.string' => 'Данные Telegram должны быть строкой',
            'init_data.min' => 'Данные Telegram слишком короткие',
            'init_data.max' => 'Данные Telegram слишком длинные',
        ];
    }
}
