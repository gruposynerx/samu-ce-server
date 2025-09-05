<?php

namespace App\Http\Requests;

use App\Enums\TicketTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class AttendanceEqualsRequest extends FormRequest
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
            'ticket_type_id' => ['required', 'integer', 'in:' . TicketTypeEnum::PRIMARY_OCCURRENCE->value . ',' . TicketTypeEnum::SECONDARY_OCCURRENCE->value],
            'requester_name' => 'required|string',
            'patient_name' => 'nullable|string',
            'city_id' => 'required|exists:cities,id',
            'neighborhood' => 'nullable|string|required_if:ticket_type_id,' . TicketTypeEnum::PRIMARY_OCCURRENCE->value . '|exclude_if:ticket_type_id,' . TicketTypeEnum::SECONDARY_OCCURRENCE->value,
            'street' => 'nullable|string|required_if:ticket_type_id,' . TicketTypeEnum::PRIMARY_OCCURRENCE->value . '|exclude_if:ticket_type_id,' . TicketTypeEnum::SECONDARY_OCCURRENCE->value,
        ];
    }
}
