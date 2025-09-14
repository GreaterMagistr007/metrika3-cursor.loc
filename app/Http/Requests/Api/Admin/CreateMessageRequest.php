<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['success', 'error', 'warning', 'info', 'system'])],
            'title' => ['nullable', 'string', 'max:255'],
            'text' => ['required', 'string', 'max:1000'],
            'url' => ['nullable', 'string', 'url', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'url', 'max:500'],
            'is_active' => ['boolean'],
            'trigger_condition' => ['nullable', 'array'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*.type' => ['required', 'string', Rule::in(['user', 'cabinet', 'all'])],
            'recipients.*.id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $recipients = $this->input('recipients', []);
            
            foreach ($recipients as $index => $recipient) {
                if ($recipient['type'] === 'user' && $recipient['id']) {
                    if (!\App\Models\User::where('id', $recipient['id'])->exists()) {
                        $validator->errors()->add(
                            "recipients.{$index}.id",
                            "Пользователь с ID {$recipient['id']} не найден"
                        );
                    }
                }
                
                if ($recipient['type'] === 'cabinet' && $recipient['id']) {
                    if (!\App\Models\Cabinet::where('id', $recipient['id'])->exists()) {
                        $validator->errors()->add(
                            "recipients.{$index}.id",
                            "Кабинет с ID {$recipient['id']} не найден"
                        );
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Тип сообщения должен быть одним из: success, error, warning, info, system',
            'recipients.required' => 'Необходимо указать получателей сообщения',
            'recipients.*.type.in' => 'Тип получателя должен быть: user, cabinet или all',
        ];
    }
}
