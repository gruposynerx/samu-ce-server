<?php

namespace App\Http\Requests;

use App\Enums\UnitTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUnitRequest extends FormRequest
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
            'unit_type_id' => ['required', 'integer', new Enum(UnitTypeEnum::class)],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'national_health_registration' => ['required', 'string', 'max:40', 'unique:units,national_health_registration'],
            'street' => ['nullable', 'string', 'max:100'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'zip_code' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'complement' => ['nullable', 'string'],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'company_registration_number' => ['nullable', 'string', 'max:40'],
            'company_name' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_type_id.required' => __('request_messages/store_unit.unit_type_id.required'),
            'unit_type_id.integer' => __('request_messages/store_unit.unit_type_id.integer'),
            'unit_type_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_unit.unit_type_id.enum'),
            'city_id.required' => __('request_messages/store_unit.city_id.required'),
            'city_id.integer' => __('request_messages/store_unit.city_id.integer'),
            'city_id.exists' => __('request_messages/store_unit.city_id.exists'),
            'name.required' => __('request_messages/store_unit.name.required'),
            'name.string' => __('request_messages/store_unit.name.string'),
            'name.max' => __('request_messages/store_unit.name.max'),
            'national_health_registration.required' => __('request_messages/store_unit.national_health_registration.required'),
            'national_health_registration.string' => __('request_messages/store_unit.national_health_registration.string'),
            'national_health_registration.max' => __('request_messages/store_unit.national_health_registration.max'),
            'national_health_registration.unique' => __('request_messages/store_unit.national_health_registration.unique'),
            'street.string' => __('request_messages/store_unit.street.string'),
            'street.max' => __('request_messages/store_unit.street.max'),
            'house_number.string' => __('request_messages/store_unit.house_number.string'),
            'house_number.max' => __('request_messages/store_unit.house_number.max'),
            'zip_code.string' => __('request_messages/store_unit.zip_code.string'),
            'zip_code.max' => __('request_messages/store_unit.zip_code.max'),
            'neighborhood.string' => __('request_messages/store_unit.neighborhood.string'),
            'neighborhood.max' => __('request_messages/store_unit.neighborhood.max'),
            'complement.string' => __('request_messages/store_unit.complement.string'),
            'latitude.string' => __('request_messages/store_unit.latitude.string'),
            'longitude.string' => __('request_messages/store_unit.longitude.string'),
            'telephone.string' => __('request_messages/store_unit.telephone.string'),
            'telephone.max' => __('request_messages/store_unit.telephone.max'),
            'company_registration_number.string' => __('request_messages/store_unit.company_registration_number.string'),
            'company_registration_number.max' => __('request_messages/store_unit.company_registration_number.max'),
            'company_name.string' => __('request_messages/store_unit.company_name.string'),
        ];
    }
}
