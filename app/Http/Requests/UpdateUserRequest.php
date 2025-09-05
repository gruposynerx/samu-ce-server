<?php

namespace App\Http\Requests;

use App\Enums\GenderCodeEnum;
use App\Enums\StreetTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'national_health_card' => ['nullable', 'string', 'max:40', 'cns'],
            'birthdate' => ['required', 'date'],
            'gender_code' => ['required', 'string', new Enum(GenderCodeEnum::class)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('id'))],
            'identifier' => ['required', 'string', 'max:11', 'cpf', Rule::unique('users', 'identifier')->ignore($this->route('id'))],
            'phone' => ['required', 'string', 'max:11'],
            'whatsapp' => ['nullable', 'string', 'max:11'],
            'city_id' => ['required', 'numeric', 'exists:cities,id'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:200'],
            'street_type' => ['required', 'integer', new Enum(StreetTypeEnum::class)],
            'house_number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'council_number' => ['nullable', 'string', 'max:50'],
            'cbo' => ['required', 'string', 'max:10'],
            'profiles' => ['required', 'array', 'min:1'],
            'profiles.*.urc_id' => ['required', 'uuid', 'exists:urgency_regulation_centers,id'],
            'profiles.*.role_id' => ['required', 'uuid', 'exists:roles,id'],
            'mobile_access' => ['sometimes', 'boolean'],
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
            'name.required' => 'O campo nome completo é obrigatório.',
            'name.string' => 'O campo nome completo deve ser uma string.',
            'name.max' => 'O campo nome completo não deve ter mais que 255 caracteres.',
            'national_health_card.string' => 'O campo cartão nacional de saúde deve ser uma string.',
            'national_health_card.max' => 'O campo cartão nacional de saúde não deve ter mais que 40 caracteres.',
            'birthdate.required' => 'O campo data de nascimento é obrigatório.',
            'birthdate.date' => 'O campo data de nascimento deve ser uma data.',
            'gender_code.required' => 'O campo Sexo é obrigatório.',
            'gender_code.string' => 'O campo Sexo deve ser uma string.',
            'gender_code.enum' => 'O campo Sexo deve ser um caso válido.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.string' => 'O campo e-mail deve ser uma string.',
            'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido.',
            'email.max' => 'O campo e-mail não deve ter mais que 255 caracteres.',
            'identifier.required' => 'O campo CPF é obrigatório.',
            'identifier.string' => 'O campo CPF deve ser uma string.',
            'identifier.max' => 'O campo CPF não deve ter mais que 11 caracteres.',
            'phone.required' => 'O campo telefone é obrigatório.',
            'phone.string' => 'O campo telefone deve ser uma string.',
            'phone.max' => 'O campo telefone não deve ter mais que 11 caracteres.',
            'whatsapp.string' => 'O campo whatsapp deve ser uma string.',
            'whatsapp.max' => 'O campo whatsapp não deve ter mais que 11 caracteres.',
            'city_id.required' => 'O campo cidade é obrigatório.',
            'city_id.numeric' => 'O campo cidade deve ser um número.',
            'city_id.exists' => 'O campo cidade deve existir.',
            'neighborhood.required' => 'O campo bairro é obrigatório.',
            'neighborhood.string' => 'O campo bairro deve ser uma string.',
            'neighborhood.max' => 'O campo bairro não deve ter mais que 100 caracteres.',
            'street.required' => 'O campo rua é obrigatório.',
            'street.string' => 'O campo rua deve ser uma string.',
            'street.max' => 'O campo rua não deve ter mais que 200 caracteres.',
            'street_type.required' => 'O campo tipo de logradouro é obrigatório.',
            'street_type.integer' => 'O campo tipo de logradouro deve ser um número.',
            'street_type.enum' => 'O campo tipo de logradouro deve ser um caso válido.',
            'house_number.required' => 'O campo número da casa é obrigatório.',
            'house_number.string' => 'O campo número da casa deve ser uma string.',
            'house_number.max' => 'O campo número da casa não deve ter mais que 20 caracteres.',
            'complement.string' => 'O campo complemento deve ser uma string.',
            'complement.max' => 'O campo complemento não deve ter mais que 255 caracteres.',
            'council_number.string' => 'O campo número do conselho deve ser uma string.',
            'council_number.max' => 'O campo número do conselho não deve ter mais que 50 caracteres.',
            'cbo.required' => 'O campo CBO é obrigatório.',
            'cbo.string' => 'O campo CBO deve ser uma string.',
            'cbo.max' => 'O campo CBO não deve ter mais que 10 caracteres.',
            'profiles.required' => 'É necessário informar um perfil.',
            'profiles.array' => 'O campo perfis deve ser um array.',
            'profiles.min' => 'É necessário informar pelo menos um perfil.',
            'profiles.*.urc_id.required' => 'Informe a unidade.',
            'profiles.*.urc_id.uuid' => 'A unidade não existe.',
            'profiles.*.urc_id.exists' => 'A unidade não existe.',
            'profiles.*.role_id.required' => 'Informe o perfil.',
            'profiles.*.role_id.uuid' => 'O perfil não existe.',
            'profiles.*.role_id.exists' => 'O perfil não existe.',
            'mobile_access.boolean' => 'O campo acesso mobile deve ser um booleano.',
        ];
    }
}
