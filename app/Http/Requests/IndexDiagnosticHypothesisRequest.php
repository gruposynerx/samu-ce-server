<?php

namespace App\Http\Requests;

use App\Enums\NatureTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Query parameters
 */
class IndexDiagnosticHypothesisRequest extends FormRequest
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
            'search' => ['nullable', 'string'],
            'nature_types' => ['nullable', 'array'],
            'nature_types.*' => ['integer', new Enum(NatureTypeEnum::class)],
            'load_nature_types' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer'],
            'filter_by_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.string' => 'A busca deve ser uma string.',
        ];
    }
}
