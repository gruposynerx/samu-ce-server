<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class CheckAuthRequest extends FormRequest
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
            'identifier' => 'required|cpf|string|exists:users,identifier',
            'password' => 'required|string',
            'mobile_detected' => 'sometimes|string',
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
            'identifier.required' => 'O campo CPF é obrigatório.',
            'identifier.string' => 'O campo CPF deve ser uma string.',
            'identifier.cpf' => 'O CPF informado é inválido.',
            'identifier.exists' => 'Usuário não encontrado.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.string' => 'O campo senha deve ser uma string.',
            'mobile_detected.string' => 'O campo mobile_detected deve ser uma string.',
        ];
    }
}
