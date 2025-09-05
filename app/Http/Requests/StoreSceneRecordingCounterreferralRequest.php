<?php

namespace App\Http\Requests;

use App\Enums\CounterreferralReasonTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSceneRecordingCounterreferralRequest extends FormRequest
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
            'scene_recording_id' => 'required|uuid|exists:scene_recordings,id',
            'unit_destination_id' => 'required|uuid|exists:units,id',
            'reason_id' => ['required', 'integer', new Enum(CounterreferralReasonTypeEnum::class)],
            'destination_unit_contact' => ['nullable', 'string'],
        ];
    }

    public function attributes()
    {
        return [
            'unit_destination_id' => 'nova unidade de destino',
            'reason_id' => 'motivo',
        ];
    }
}
