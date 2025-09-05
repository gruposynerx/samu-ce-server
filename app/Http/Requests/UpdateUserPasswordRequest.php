<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPasswordRequest extends FormRequest
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
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'current_password' => ['nullable', 'string', 'current_password:api'],
            'new_password' => ['required', 'string', 'confirmed', 'min:8', 'regex:/[!@#$%^&*(),.?":{}|<>]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.string' => 'O campo usuário deve ser uma string.',
            'user_id.exists' => 'O usuário não existe.',
            'current_password.string' => 'O campo senha atual deve ser uma string.',
            'current_password.current_password' => 'A senha atual não confere.',
            'new_password.required' => 'O campo nova senha deve ser obrigatório.',
            'new_password.string' => 'O campo nova senha dee ser uma string.',
            'new_password.confirmed' => 'O campo confirmação de senha não confere.',
            'new_password.min' => 'O campo nova senha deve ter no mínimo 8 caracteres.',
            'new_password.regex' => 'O campo nova senha deve conter pelo menos uma letra maiúscula, um número e um caractere especial.',
            'new_password_confirmation.required' => 'O campo confirmação de senha é obrigatório.',
            'new_password_confirmation.string' => 'O campo confirmação de senha deve ser uma string.',
            'new_password_confirmation.min' => 'O campo confirmação de senha deve ter no mínimo 8 caracteres.',
            'new_password_confirmation.regex' => 'O campo confirmação de senha deve conter pelo menos uma letra maiúscula, um número e um caractere especial.',
        ];
    }
}
