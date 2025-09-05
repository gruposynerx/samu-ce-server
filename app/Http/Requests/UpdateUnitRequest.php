<?php

namespace App\Http\Requests;

use App\Enums\UnitTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUnitRequest extends FormRequest
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
            'national_health_registration' => ['required', 'string', 'max:40', Rule::unique('units', 'national_health_registration')->ignore($this->route('id'))],
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
            'unit_type_id.required' => 'O tipo de unidade é obrigatório.',
            'unit_type_id.integer' => 'O tipo de unidade deve ser um número inteiro.',
            'unit_type_id.enum' => 'O tipo de unidade deve ser um caso válido.',
            'city_id.required' => 'É obrigatório informar a cidade.',
            'city_id.integer' => 'O ID cidade deve ser um número inteiro.',
            'city_id.exists' => 'A cidade deve existir.',
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser uma string.',
            'name.max' => 'O nome não deve ter mais que 255 caracteres.',
            'national_health_registration.required' => 'O CNES é obrigatório.',
            'national_health_registration.string' => 'O CNES deve ser uma string.',
            'national_health_registration.max' => 'O CNES não deve ter mais que 40 caracteres.',
            'national_health_registration.unique' => 'O CNES informado já está em uso.',
            'street.string' => 'A rua deve ser uma string.',
            'street.max' => 'A rua não deve ter mais que 100 caracteres.',
            'house_number.string' => 'O número residêncial deve ser uma string.',
            'house_number.max' => 'O número residêncial não deve ter mais que 20 caracteres.',
            'zip_code.string' => 'O CEP deve ser uma string.',
            'zip_code.max' => 'O CEP não deve ter mais que 255 caracteres.',
            'neighborhood.string' => 'O bairro deve ser uma string.',
            'neighborhood.max' => 'O bairro não deve ter mais que 100 caracteres.',
            'complement.string' => 'O complemento deve ser uma string.',
            'latitude.string' => 'A latitude deve ser uma string.',
            'longitude.string' => 'A longitude deve ser uma string.',
            'telephone.string' => 'O telefone deve ser uma string.',
            'telephone.max' => 'O telefone não deve ter mais que 40 caracteres.',
            'company_registration_number.string' => 'O CNPJ deve ser uma string.',
            'company_registration_number.max' => 'O CNPJ não deve ter mais que 40 caracteres.',
            'company_name.string' => 'A razão social deve ser uma string.',
        ];
    }
}
