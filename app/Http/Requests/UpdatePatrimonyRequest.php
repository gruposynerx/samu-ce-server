<?php

namespace App\Http\Requests;

use App\Enums\PatrimonyStatusEnum;
use App\Enums\PatrimonyTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdatePatrimonyRequest extends FormRequest
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
            'patrimony_type_id' => ['required', 'integer', new Enum(PatrimonyTypeEnum::class)],
            'identifier' => ['required', 'string', 'max:255',
                Rule::unique('patrimonies', 'identifier')
                    ->where('patrimony_type_id', $this->patrimony_type_id)
                    ->ignore($this->route('id')),
            ],
            'patrimony_status_id' => ['required', new Enum(PatrimonyStatusEnum::class)],
            'vehicle_id' => ['present', 'nullable', 'exists:vehicles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'patrimony_type_id.required' => 'O campo tipo de patrimônio é obrigatório',
            'patrimony_type_id.integer' => 'O campo tipo de patrimônio deve ser um inteiro',
            'patrimony_type_id.enum' => 'O campo tipo de patrimônio deve ser um caso válido',
            'identifier.required' => 'O campo identificador é obrigatório',
            'identifier.string' => 'O campo identificador deve ser uma string',
            'identifier.max' => 'O campo identificador deve ter no máximo 255 caracteres',
            'identifier.unique' => 'O campo identificador deve ser único',
            'is_active.boolean' => 'O campo status deve ser do tipo boolean',
            'vehicle_id.exists' => 'O veículo informado não existe',
        ];
    }
}
