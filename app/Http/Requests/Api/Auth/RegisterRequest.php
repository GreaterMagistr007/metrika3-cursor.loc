<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^\+7\d{10}$/',
                'unique:users,phone'
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255'
            ],
            'telegram_id' => [
                'nullable',
                'integer',
                'unique:users,telegram_id'
            ],
            'telegram_data' => [
                'nullable',
                'array'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Номер телефона обязателен',
            'phone.regex' => 'Номер телефона должен быть в формате +7XXXXXXXXXX (11 цифр после +7)',
            'phone.unique' => 'Пользователь с таким номером телефона уже существует',
            'name.required' => 'Имя обязательно',
            'name.min' => 'Имя должно содержать минимум 2 символа',
            'name.max' => 'Имя не должно превышать 255 символов',
            'telegram_id.unique' => 'Пользователь с таким Telegram ID уже существует',
        ];
    }
}
