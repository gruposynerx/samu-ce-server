<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Http\Requests\StoreOtherAttendanceRequest;
use App\Models\OtherAttendance;
use App\Models\Patient;
use App\Traits\TicketCommons;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Demais Ocorrências', description: 'Seção responsável pela gestão de chamados do tipo "Trote, Informação, Engano, Queda de Ligação e Contato com a Equipe do SAMU"')]
class OtherAttendanceController extends Controller
{
    use TicketCommons;

    /**
     * POST api/ticket/other-attendance
     *
     * Realiza o cadastro de um chamado com o tipo "Trote, Informação, Engano, Queda de Ligação e Contato com a Equipe do SAMU".
     */
    public function store(StoreOtherAttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $ticketId = $this->storeTicket($data)['id'];

        $patientId = null;
        if (isset($data['patients'])) {
            $patientId = Patient::create($data['patients'][0])['id'];
        }

        $formsSetting = auth()->user()->currentUrgencyRegulationCenter->formsSetting;
        $isLateOccurrence = $formsSetting->enable_late_occurrence ? $data['is_late_occurrence'] ?? false : false;

        $otherAttendance = OtherAttendance::create($data);

        $otherAttendance->attendable()->create([
            'patient_id' => $patientId,
            'created_by' => auth()->user()->id,
            'ticket_id' => $ticketId,
            'attendance_status_id' => AttendanceStatusEnum::COMPLETED->value,
            'is_late_occurrence' => $isLateOccurrence,
        ]);

        return $this->defaultReturn();
    }
}
