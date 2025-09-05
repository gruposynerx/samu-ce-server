<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserRoleFromUrgencyRegulationCenter extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|uuid|exists:users,id',
            'urgency_regulation_centers' => 'required|array',
            'urgency_regulation_centers.*.urc_id' => 'required|uuid|exists:urgency_regulation_centers,id',
            'urgency_regulation_centers.*.roles' => 'required|array',
            'urgency_regulation_centers.*.roles.*' => 'required|uuid|exists:roles,id',
        ];
    }
}
