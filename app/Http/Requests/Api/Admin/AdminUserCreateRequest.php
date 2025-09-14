<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class AdminUserCreateRequest extends FormRequest
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
            'phone' => 'required|string|max:20|unique:admin_users,phone',
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,super_admin',
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
            'phone.unique' => 'Администратор с таким номером телефона уже существует',
            'name.required' => 'Имя обязательно',
            'role.required' => 'Роль обязательна',
            'role.in' => 'Роль должна быть admin или super_admin',
        ];
    }
}
