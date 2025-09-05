<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class ShowByAttendanceRadioOperationRequest extends FormRequest
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
            'show_all_fleet_histories' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'show_all_fleet_histories.boolean' => 'O parâmetro deve ser um booleano.',
        ];
    }
}
