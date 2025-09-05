<?php

namespace App\Http\Requests;

use App\Enums\PatrimonyStatusEnum;
use App\Rules\FleetOccupationExists;
use App\Rules\OccupeVehicleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RadioOperationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicles = collect($this->radio_operation_fleet)->pluck('vehicle_id')->toArray();

        return [
            'radio_operation_fleet' => [
                'required',
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

            'attendance_id' => 'required|exists:attendances,id',
            'awaiting_fleet_confirmation' => 'sometimes|boolean',
            'vehicle_requested_at' => 'sometimes|nullable|before_or_equal:now',
            'vehicle_confirmed_at' => 'sometimes|nullable|before_or_equal:now|after_or_equal:vehicle_requested_at',
            'vehicle_dispatched_at' => 'sometimes|nullable|before_or_equal:now|after_or_equal:vehicle_requested_at',
            'arrived_to_site_at' => 'sometimes|nullable|before_or_equal:now|after_or_equal:vehicle_dispatched_at',
            'left_from_site_at' => 'sometimes|nullable|before_or_equal:now|after_or_equal:arrived_to_site_at',
            'arrived_to_destination_at' => 'sometimes|nullable|before_or_equal:now|after_or_equal:left_from_site_at',
            'release_from_destination_at' => 'sometimes|nullable|before_or_equal:now|after_or_equal:arrived_to_destination_at',
            'vehicle_released_at' => 'sometimes|nullable|before_or_equal:now|after_or_equal:arrived_to_destination_at',
            'sent_by_app' => 'sometimes|boolean',

            'notes' => 'sometimes|present|array',
            'notes.*.datetime' => 'sometimes|nullable',
            'notes.*.patrimony_id' => ['sometimes', 'nullable', Rule::exists('patrimonies', 'id')->whereNot('patrimony_status_id', PatrimonyStatusEnum::UNAVAILABLE)->whereIn('vehicle_id', $vehicles)],
            'notes.*.responsible_professional' => 'sometimes|nullable|string',
            'notes.*.observation' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return parent::messages() + [
            'awaiting_fleet_confirmation.boolean' => 'Você deve indicar se a VTR deve aguardar a confirmação ou não.',
            'vehicle_requested_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
            'vehicle_confirmed_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
            'vehicle_dispatched_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
            'arrived_to_site_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
            'left_from_site_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
            'arrived_to_destination_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
            'release_from_destination_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
            'vehicle_released_at.before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a agora.',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'attendance_id' => 'atendimento',
            'radio_operation_fleet' => 'frota',
            'radio_operation_fleet.*.vehicle_id' => 'VTR',
            'radio_operation_fleet.*.users' => 'tripulação',
            'radio_operation_fleet.*.users.*.id' => 'usuário',
            'radio_operation_fleet.*.users.*.occupation_id' => 'ocupação do profissional',
            'radio_operation_fleet.*.users.*.external_professional' => 'profissional externo',
            'awaiting_fleet_confirmation' => 'aguardar confirmação',
            'vehicle_requested_at' => 'solicitação VTR',
            'vehicle_confirmed_at' => 'confirmação VTR',
            'vehicle_dispatched_at' => 'saída VTR',
            'vehicle_released_at' => 'liberação VTR',
            'arrived_to_site_at' => 'chegada local',
            'left_from_site_at' => 'saída local',
            'arrived_to_destination_at' => 'chegada destino',
            'release_from_destination_at' => 'liberação destino',
            'notes' => 'outras informações',
            'notes.*.datetime' => 'data e hora',
            'notes.*.patrimony_id' => 'equipamento',
            'notes.*.responsible_professional' => 'profissional responsável',
            'notes.*.observation' => 'observação',
        ];
    }
}
