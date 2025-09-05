<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowAttendanceDataRequest extends FormRequest
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
            'load_scene_recordings' => 'nullable|boolean',
            'load_latest_vehicle_status_history' => 'nullable|boolean',
            'full_detail' => 'nullable|boolean',
            'load_father_link' => 'nullable|boolean',
        ];
    }
}
