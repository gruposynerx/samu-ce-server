<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthDeviceRequest extends FormRequest
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
            'identifier' => 'required|string|exists:users,identifier|cpf',
            'password' => 'required|string',
            'urc_id' => 'required|uuid|exists:urgency_regulation_centers,id',
            'role_id' => [
                'required',
                'uuid',
                Rule::exists('user_roles', 'role_id')->where(function ($query) {
                    $query->where('urc_id', $this->urc_id);
                }),
            ],
            'pin' => 'required|string|size:8',
        ];
    }
}
