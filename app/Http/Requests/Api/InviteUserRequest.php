<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class InviteUserRequest extends FormRequest
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
            'phone' => ['required', 'string', 'regex:/^\+7\d{10}$/'],
            'role' => ['required', 'string', 'in:admin,manager,operator'],
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
            'phone.required' => 'Номер телефона обязателен для заполнения.',
            'phone.regex' => 'Номер телефона должен быть в формате +7XXXXXXXXXX.',
            'role.required' => 'Роль пользователя обязательна для заполнения.',
            'role.in' => 'Роль должна быть одной из: admin, manager, operator.',
        ];
    }
}
