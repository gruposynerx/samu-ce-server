<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class IndexCityRequest extends FormRequest
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
            'federal_unit_id' => ['nullable', 'integer', 'exists:federal_units,id'],
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
            'search.string' => 'A busca deve ser uma string.',
            'search.max' => 'A busca deve ter no máximo 255 caracteres.',
            'federal_unit_id.integer' => 'O id da unidade federativa deve ser um inteiro.',
            'federal_unit_id.exists' => 'O id da unidade federativa deve existir.',
        ];
    }
}
