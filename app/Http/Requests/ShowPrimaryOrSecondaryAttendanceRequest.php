<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class ShowPrimaryOrSecondaryAttendanceRequest extends FormRequest
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
            'with_latest_medical_regulation' => ['sometimes', 'nullable', 'boolean'],
            'with_diagnostic_hypotheses_latest_medical_regulation' => ['sometimes', 'nullable', 'boolean', 'exclude_if:with_latest_medical_regulation,false'],
            'with_latest_scene_recording' => ['sometimes', 'nullable', 'boolean'],
            'with_diagnostic_hypotheses_latest_scene_recording' => ['sometimes', 'nullable', 'boolean', 'exclude_if:with_latest_scene_recording,false'],
        ];
    }
}
