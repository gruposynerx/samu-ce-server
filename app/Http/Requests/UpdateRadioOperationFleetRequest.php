<?php

namespace App\Http\Requests;

use App\Rules\FleetOccupationExists;
use App\Rules\OccupeVehicleType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRadioOperationFleetRequest extends FormRequest
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
            'radio_operation_fleet' => [
                'nullable',
                'bail',
                'array',
                new OccupeVehicleType(),
            ],
            'radio_operation_fleet.*.vehicle_id' => 'required|exists:vehicles,id',
            'radio_operation_fleet.*.users' => 'present|array',
            'radio_operation_fleet.*.users.*.id' => 'nullable|exists:users,id|required_without:radio_operation_fleet.*.users.*.external_professional',
            'radio_operation_fleet.*.users.*.occupation_id' => [
                'required',
                new FleetOccupationExists(),
            ],
            'radio_operation_fleet.*.users.*.external_professional' => 'nullable|required_without:radio_operation_fleet.*.users.*.id|string',
            'change_reason' => 'required|string|max:255',
            'sent_by_app' => 'sometimes|boolean',
        ];
    }

    public function attributes()
    {
        return parent::attributes() + [
            'radio_operation_fleet' => 'frota',
            'radio_operation_fleet.*.vehicle_id' => 'VTR',
            'radio_operation_fleet.*.users' => 'tripulação',
            'radio_operation_fleet.*.users.*.id' => 'usuário',
            'radio_operation_fleet.*.users.*.occupation_id' => 'ocupação do profissional',
            'radio_operation_fleet.*.users.*.external_professional' => 'profissional externo',
        ];
    }
}
