<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexSatisfactionConsultationRequest extends FormRequest
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

            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'bases' => ['nullable', 'array'],
            'bases.*' => 'uuid|exists:bases,id',
            'cru' => ['nullable', 'array'],
            'cru.*' => 'uuid|exists:urgency_regulation_centers,id',
            'cities' => ['nullable', 'array'],
            'cities.*' => 'integer|exists:cities,id',
            'groups_regional' => ['nullable', 'array'],
            'groups_regional.*' => 'integer|exists:cities,id',
        ];
    }
}
