<?php

namespace App\Http\Requests;

use App\Enums\TimeUnitEnum;
use App\Enums\NatureTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;


class AttendanceNatureConsultationRequest extends FormRequest
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
      'per_page' => 'nullable|integer',
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date',
      'cities' => ['nullable', 'array'],
      'cities.*' => 'integer|exists:cities,id',
      'nature_types' => ['nullable', 'array'],
      'nature_types.*' => [new Enum(NatureTypeEnum::class)],
      'diagnostic_hypotheses' => ['nullable', 'array'],
      'diagnostic_hypotheses.*' => 'integer|exists:diagnostic_hypotheses,id',
      'bases' => ['nullable', 'array'],
      'bases.*' => 'uuid|exists:bases,id',
      'initial_birth_date' => 'nullable|integer',
      'final_birth_date' => 'nullable|integer',
      'time_unit_id' => ['nullable', 'integer', new Enum(TimeUnitEnum::class), 'required_with:initial_birth_date,final_birth_date'],
      'groups_regional' => ['nullable', 'array'],
      'groups_regional.*' => 'uuid|exists:regional_groups,id',
      'units_destination' => ['nullable', 'array'],
      'units_destination.*' => 'uuid|exists:units,id',
    ];
  }
}
