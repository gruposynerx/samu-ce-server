<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class AttendanceBaseStatusesRequest extends FormRequest
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
            'urc_id' => 'nullable|exists:urgency_regulation_centers,id|exclude_if:totem_dashboard,true|required_without:totem_dashboard',
            'totem_dashboard' => 'nullable|boolean|exclude_with:urc_id|required_without:urc_id',
        ];
    }
}
