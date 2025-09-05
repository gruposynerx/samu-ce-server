<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShowAttendanceDataRequest;
use App\Http\Resources\AttendanceDataResource;
use App\Models\Attendance;
use App\Models\PrimaryAttendance;
use App\Models\SecondaryAttendance;
use App\Scopes\UrcScope;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Dados da Ocorrência')]
class AttendanceDataController extends Controller
{
    /**
     * GET api/attendance/{id}
     *
     * Retorna os dados da ocorrência com base no ID do atendimento.
     *
     * @urlParam id string required ID do atendimento (tabela attendances).
     */
    public function show(ShowAttendanceDataRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = Attendance::with([
            'patient',
            'ticket:tickets.id,tickets.ticket_sequence_per_urgency_regulation_center,tickets.opening_at,tickets.ticket_type_id,tickets.city_id,tickets.multiple_victims,tickets.number_of_victims,tickets.requester_id,tickets.created_by,tickets.created_at',
            'ticket.city:cities.id,cities.name',
            'ticket.createdBy:users.id,users.name',
            'createdBy:users.id,users.name',
            'ticket.requester:requesters.id,requesters.name,requesters.primary_phone,requesters.secondary_phone,requesters.requester_type_id,requesters.council_number',
            'latestMedicalRegulation:medical_regulations.id,medical_regulations.attendance_id,medical_regulations.created_at,medical_regulations.created_by,medical_regulations.medical_regulation,medical_regulations.destination_unit_contact',
            'latestMedicalRegulation.createdBy:users.id,users.name',
            'radioOperation:radio_operations.id,radio_operations.attendance_id,radio_operations.created_by,radio_operations.vehicle_requested_at,radio_operations.vehicle_dispatched_at,radio_operations.vehicle_confirmed_at,radio_operations.vehicle_released_at,radio_operations.arrived_to_site_at,radio_operations.left_from_site_at,radio_operations.arrived_to_destination_at,radio_operations.release_from_destination_at,radio_operations.created_at,radio_operations.updated_at',
            'radioOperation.creator:users.id,users.name,users.council_number',
            'radioOperation.fleets.vehicle',
            'radioOperation.fleets.vehicle.vehicleType',
            'radioOperation.fleets.vehicle.base' => fn ($q) => $q->select('id', 'city_id')->withoutGlobalScope(UrcScope::class),
            'radioOperation.fleets.vehicle.base.city',
            'radioOperation.fleets.users:users.id,users.name',
            'radioOperation.fleets.externalProfessionals',
            'radioOperation.histories.creator:id,name',
            'observations.creator:users.id,users.name',
            'observations.roleCreator:roles.id,roles.name',
            'sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.unit_destination_id,scene_recordings.diagnostic_hypothesis_id,scene_recordings.destination_unit_contact',
            'sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'sceneRecording.latestDestinationUnitHistory.unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
            'sceneRecording.latestDestinationUnitHistory.unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
            'sceneRecording.diagnosticHypotheses',
            'latestMedicalRegulation.diagnosticHypotheses',
            'cancellation.creator',
            'attendable' => function (MorphTo $query) {
                $query->morphWith([
                    SecondaryAttendance::class => [
                        'unitOrigin',
                        'unitOrigin.city:cities.id,cities.name,cities.federal_unit_id',
                        'unitOrigin.city.federalUnit:federal_units.id,federal_units.uf',
                        'unitDestination',
                        'unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
                        'unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
                    ],
                    PrimaryAttendance::class => [
                        'unitDestination:id,name',
                    ],
                ]);
            },
        ])
            ->when(!empty($data['full_detail']), function ($query) {
                $query->with([
                    'sceneRecording.icd',
                    'sceneRecording.diagnosticHypotheses',
                    'sceneRecording.metrics',
                    'sceneRecording.wounds',
                    'sceneRecording.procedures.procedure',
                    'sceneRecording.medicines.medicine',
                    'sceneRecording.conducts',
                    'sceneRecording.latestDestinationUnitHistory.unitDestination',
                    'sceneRecording.createdBy:id,name',

                    'radioOperation.vehicles.vehicleType',
                    'radioOperation.vehicles.base' => fn ($q) => $q->select('id', 'city_id')->withoutGlobalScope(UrcScope::class),
                    'radioOperation.vehicles.base.city',
                    'radioOperation.fleets.vehicle.latestVehicleStatusHistory:id,attendance_id,vehicle_id,vehicle_type_id,base_id,city_id',
                    'radioOperation.fleets.vehicle.latestVehicleStatusHistory.vehicle:id,code',
                    'radioOperation.fleets.vehicle.latestVehicleStatusHistory.vehicleType',
                    'radioOperation.fleets.vehicle.latestVehicleStatusHistory.vehicleCity:id,name',
                    'radioOperation.fleets.users:users.id,users.name',
                    'radioOperation.fleets.creator:users.id,users.name',
                    'radioOperation.notes.patrimony',
                    'radioOperation.attendance:id',
                    'radioOperation.attendance.sceneRecording:id',
                    'radioOperation.fleetHistories',
                    'radioOperation.fleetHistories.creator:id,name',
                ]);
            })
            ->when(!empty($data['load_latest_vehicle_status_history']), function ($query) {
                $query->with([
                    'latestVehicleStatusHistory:vehicle_status_histories.id,vehicle_status_histories.attendance_id,vehicle_status_histories.vehicle_id,vehicle_status_histories.vehicle_type_id,vehicle_status_histories.base_id,vehicle_status_histories.city_id',
                    'latestVehicleStatusHistory.vehicle:id,code,tracking_system_id',
                    'latestVehicleStatusHistory.vehicleType',
                    'latestVehicleStatusHistory.vehicleCity:id,name',
                ]);
            })
            ->when(!empty($data['load_scene_recordings']), function ($query) {
                $query->with([
                    'sceneRecordings' => fn ($q) => $q->orderBy('created_at'),
                    'sceneRecordings.icd',
                    'sceneRecordings.diagnosticHypotheses',
                    'sceneRecordings.metrics',
                    'sceneRecordings.wounds',
                    'sceneRecordings.procedures.procedure',
                    'sceneRecordings.medicines.medicine',
                    'sceneRecordings.conducts',
                    'sceneRecordings.antecedentsTypes.antecedentType',
                    'sceneRecordings.antecedentsTypes.antecedentType',
                    'sceneRecordings.latestDestinationUnitHistory.unitDestination',
                    'sceneRecordings.createdBy:users.id,users.name',
                ]);
            })
            ->when(!empty($data['load_father_link']), function ($query) {
                $query->with([
                    'fatherLink:attendances.id,attendances.ticket_id,attendances.attendance_sequence_per_ticket',
                    'fatherLink.ticket:tickets.id,tickets.ticket_sequence_per_urgency_regulation_center',
                ]);
            })
            ->findOrFail($id);

        return response()->json(new AttendanceDataResource($result));
    }
}
