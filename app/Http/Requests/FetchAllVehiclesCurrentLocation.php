<?php

namespace App\Http\Requests;

use App\Enums\VehicleStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FetchAllVehiclesCurrentLocation extends FormRequest
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
            'vehicle_statuses_to_search' => ['array', 'nullable'],
            'vehicle_statuses_to_search.*' => [new Enum(VehicleStatusEnum::class)],
            'search' => ['nullable', 'string'],
        ];
    }
}
