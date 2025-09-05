<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class IndexUserRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'cbo' => ['nullable', 'string'],
            'role_ids' => ['nullable', 'string'],
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
            'search.string' => 'O campo busca deve ser uma string.',
            'search.max' => 'O campo busca deve ter no máximo 255 caracteres.',
            'cbo.string' => 'O campo cbo deve ser uma string.',
            'role_ids.string' => 'O campo role_ids deve ser uma string.',
        ];
    }
}
