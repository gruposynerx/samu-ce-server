<?php

namespace App\Http\Requests;

use App\Enums\NatureTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class DiagnosticHypothesisRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                Rule::unique('diagnostic_hypotheses', 'name')->ignore($this->route('id')),
            ],
            'nature_types_id' => ['required', 'array'],
            'nature_types_id.*' => [new Enum(NatureTypeEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da hipótese diagnóstica é obrigatório.',
            'name.string' => 'O nome da hipótese diagnóstica deve ser uma string.',
            'name.unique' => 'Hipótese diagnóstica já cadastrada.',
            'nature_types_id.required' => 'A natureza é obrigatória.',
            'nature_types_id.array' => 'A natureza deve ser uma matriz.',
            'nature_types_id.*.Illuminate\Validation\Rules\Enum' => 'A natureza deve ser válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Hipótese Diagnóstica',
            'nature_type_id' => 'Natureza',
        ];
    }
}
