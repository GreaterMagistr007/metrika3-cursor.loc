<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class AssignPermissionRequest extends FormRequest
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
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['required', 'integer', 'exists:permissions,id'],
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
            'permission_ids.required' => 'Необходимо указать права для назначения.',
            'permission_ids.array' => 'Права должны быть переданы в виде массива.',
            'permission_ids.min' => 'Необходимо указать хотя бы одно право.',
            'permission_ids.*.required' => 'Каждое право должно быть указано.',
            'permission_ids.*.integer' => 'ID права должно быть числом.',
            'permission_ids.*.exists' => 'Указанное право не существует.',
        ];
    }
}
