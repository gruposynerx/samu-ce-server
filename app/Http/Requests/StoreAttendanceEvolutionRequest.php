<?php

namespace App\Http\Requests;

use App\Models\MedicalRegulation;
use App\Models\SceneRecording;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceEvolutionRequest extends FormRequest
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
            'evolution' => 'required|string|max:5000',
            'attendance_id' => 'required|uuid|exists:attendances,id',
            'form_type_slug' => 'required|string|in:' . implode(',', [getMorphAlias(MedicalRegulation::class), getMorphAlias(SceneRecording::class)]),
        ];
    }

    public function attributes(): array
    {
        return [
            'evolution' => 'evolução',
            'form_type_slug' => 'tipo de formulário',
            'attendance_id' => 'atendimento',
        ];
    }
}
