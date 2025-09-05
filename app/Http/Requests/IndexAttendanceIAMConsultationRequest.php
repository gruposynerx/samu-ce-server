<?php

namespace App\Http\Requests;

use App\Enums\ThrombolyticIndicatedAppliedEnum;
use App\Enums\ThrombolyticIndicatedRecommendedEnum;
use App\Enums\TimeUnitEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;


class IndexAttendanceIAMConsultationRequest extends FormRequest
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
            'list_all' => 'nullable|boolean',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'bases' => ['nullable', 'array'],
            'bases.*' => 'uuid|exists:bases,id',
            'cities' => ['nullable', 'array'],
            'cities.*' => 'integer|exists:cities,id',
            'initial_birth_date' => 'nullable|integer',
            'final_birth_date' => 'nullable|integer',
            'time_unit_id' => ['nullable', 'integer', new Enum(TimeUnitEnum::class), 'required_with:initial_birth_date,final_birth_date'],
            'thrombolytic_recommended' => ['nullable', 'array'],
            'thrombolytic_recommended.*' => ['nullable', 'integer', new Enum(ThrombolyticIndicatedRecommendedEnum::class)],
            'thrombolytic_applied' => ['nullable', 'array'],
            'thrombolytic_applied.*' => ['nullable', 'integer', new Enum(ThrombolyticIndicatedAppliedEnum::class)],
            'units_origin' => ['nullable', 'array'],
            'units_origin.*' => 'uuid|exists:units,id',
            'units_destination' => ['nullable', 'array'],
            'units_destination.*' => 'uuid|exists:units,id',
        ];
    }
}
