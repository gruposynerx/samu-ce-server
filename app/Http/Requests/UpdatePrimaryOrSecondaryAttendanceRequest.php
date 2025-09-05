<?php

namespace App\Http\Requests;

use App\Enums\GenderCodeEnum;
use App\Enums\TimeUnitEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePrimaryOrSecondaryAttendanceRequest extends FormRequest
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
            'name' => 'nullable|string',
            'age' => 'required|integer',
            'time_unit_id' => ['required', 'integer', new Enum(TimeUnitEnum::class)],
            'gender_code' => ['required', 'string', new Enum(GenderCodeEnum::class)],
            'in_central_bed' => 'nullable|boolean',
            'protocol' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O nome do paciente deve ser uma string.',
            'age.integer' => 'A idade do paciente deve ser um número inteiro.',
            'age.required' => 'A idade do paciente é obrigatória.',
            'time_unit_id.integer' => 'A unidade de tempo da idade do paciente deve ser um número inteiro.',
            'time_unit_id.enum' => 'A unidade de tempo da idade do paciente não existe.',
            'time_unit_id.required' => 'A unidade de tempo da idade do paciente é obrigatória.',
            'gender_code.string' => 'O gênero do paciente deve ser uma string.',
            'gender_code.required' => 'O gênero do paciente é obrigatório.',
            'gender_code.enum' => 'O gênero do paciente não existe.',
            'in_central_bed.boolean' => 'Está na central de leitos deve ser verdadeiro ou falso.',
            'protocol.string' => 'O número do protocolo deve ser uma string.',
        ];
    }
}
