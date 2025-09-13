<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyOtpRequest extends FormRequest
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
            'phone' => [
                'required',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/', // E.164 format
                'max:20'
            ],
            'otp' => [
                'required',
                'string',
                'size:6',
                'regex:/^\d{6}$/'
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
            'phone.required' => 'Номер телефона обязателен',
            'phone.regex' => 'Номер телефона должен быть в международном формате (+7XXXXXXXXXX)',
            'phone.max' => 'Номер телефона не должен превышать 20 символов',
            'otp.required' => 'Код подтверждения обязателен',
            'otp.size' => 'Код подтверждения должен содержать 6 цифр',
            'otp.regex' => 'Код подтверждения должен содержать только цифры',
        ];
    }
}
