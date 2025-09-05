<?php

namespace App\Http\Controllers;

use App\Enums\PatrimonyStatusEnum;
use App\Http\Requests\ReleasePatrimonyRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\PatrimonyRetainmentsResource;
use App\Models\PatrimonyRetainmentHistory;
use App\Models\RadioOperationNote;
use App\Models\SecondaryAttendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Patrimônios', description: 'Gestão de patrimônios')]
#[Subgroup('Retenção de Patrimônios', description: 'Seção responsável pela gestão de retenção de patrimônios')]
class PatrimonyRetainmentsController extends Controller
{
    /**
     * GET api/patrimony-retainements
     *
     * Retorna uma lista paginada de equipamentos retidos.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $search = $request->get('search');
        $results = PatrimonyRetainmentHistory::select(['id', 'patrimony_id', 'retained_at', 'retained_by', 'attendance_id'])
            ->with([
                'patrimony.patrimonyType',
                'patrimony.vehicle:id,vehicle_type_id,license_plate',
                'patrimony.vehicle.vehicleType',
                'attendance',
                'attendance.ticket:id,ticket_sequence_per_urgency_regulation_center',
                'retainer:id,name',
                'attendance.sceneRecording:id,unit_destination_id',
                'attendance.sceneRecording.unitDestination:id,name',
                'attendance.attendable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        app(SecondaryAttendance::class)->getMorphClass() => ['unitDestination:id,name'],
                    ]);
                },
            ])
            ->whereNull('released_at')
            ->when(!empty($search), function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('patrimony.patrimonyType', fn ($query) => $query->whereRaw('unaccent(name) ilike unaccent(?)', "%$search%"))
                        ->orWhereHas('patrimony.vehicle', fn (Builder $query) => $query->whereRaw('unaccent(license_plate) ilike unaccent(?)', "%$search%"))
                        ->orWhereHas('patrimony.vehicle.vehicleType', fn (Builder $query) => $query->whereRaw('unaccent(name) ilike unaccent(?)', "%$search%"))
                        ->orWhereHas('retainer', fn (Builder $query) => $query->whereRaw('unaccent(name) ilike unaccent(?)', "%$search%"))
                        ->orWhereHas('attendance', function (Builder $query) use ($search) {
                            $searchNumbers = explode('/', $search);

                            $ticketSequence = $searchNumbers[0] ?? null;
                            $attendanceSequence = $searchNumbers[1] ?? null;

                            $query->where(function (Builder $query) use ($search, $ticketSequence, $attendanceSequence) {
                                $query->when(!empty($ticketSequence) && is_numeric($ticketSequence), function ($query) use ($ticketSequence, $attendanceSequence) {
                                    $query->where(function ($query) use ($ticketSequence, $attendanceSequence) {
                                        $query->whereHas('ticket', fn (Builder $query) => $query->where('ticket_sequence_per_urgency_regulation_center', $ticketSequence))
                                            ->when(!empty($attendanceSequence) && is_numeric($attendanceSequence), function ($query) use ($attendanceSequence) {
                                                $query->where('attendance_sequence_per_ticket', $attendanceSequence);
                                            });
                                    });
                                })
                                    ->orWhere(function (Builder $query) use ($search) {
                                        $query->whereHas('sceneRecording.unitDestination', fn (Builder $query) => $query->whereRaw('unaccent(name) ilike unaccent(?)', "%$search%"))
                                            ->orWhereHasMorph(
                                                'attendable',
                                                [app(SecondaryAttendance::class)->getMorphClass()],
                                                fn (Builder $query) => $query->whereHas('unitDestination', fn ($q) => $q->whereRaw('unaccent(name) ilike unaccent(?)', "%$search%"))
                                            );
                                    });
                            });

                        });
                });
            })
            ->orderBy('created_at')
            ->paginate(10);

        return PatrimonyRetainmentsResource::collection($results);
    }

    public function release(ReleasePatrimonyRequest $request, string $id): Response
    {
        $retainment = PatrimonyRetainmentHistory::findOrFail($id);

        if (PatrimonyStatusEnum::tryFrom($retainment->patrimony->patrimony_status_id) !== PatrimonyStatusEnum::RETAINED) {
            throw ValidationException::withMessages(['patrimony' => 'O patrimônio não está retido.']);
        }

        $retainment->update([
            'released_at' => $request->post('datetime'),
            'released_by' => auth()->id(),
        ]);

        $retainment->patrimony->update(['patrimony_status_id' => PatrimonyStatusEnum::AVAILABLE]);

        RadioOperationNote::where([
            'radio_operation_id' => $retainment->radio_operation_id,
            'patrimony_id' => $retainment->patrimony_id,
        ])->update(['patrimony_id' => null]);

        return response()->noContent();
    }
}
