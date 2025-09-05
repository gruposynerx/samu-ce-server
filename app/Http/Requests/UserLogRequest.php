<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UserLogRequest extends FormRequest
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
            'search' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:' . Carbon::parse($this->start_date)->addMonth(),
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'A pesquisa deve ser uma string.',
            'start_date.date' => 'A data de início deve ser uma data válida.',
            'end_date.date' => 'A data final deve ser uma data válida.',
            'end_date.after' => 'A data final deve ser posterior à data de início',
            'end_date.before_or_equal' => 'A data final deve ser anterior ou igual à ' . Carbon::parse($this->start_date)->addMonth()->format('d/m/Y') . '.',
        ];
    }
}
