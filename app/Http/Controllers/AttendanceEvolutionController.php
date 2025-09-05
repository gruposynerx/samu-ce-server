<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceEvolutionRequest;
use App\Models\Attendance;
use App\Models\MedicalRegulation;
use App\Models\SceneRecording;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Evoluções do Atendimento', description: 'Seção responsável pela gestão de evoluções do atendimento')]
class AttendanceEvolutionController extends Controller
{
    /**
     * POST api/attendance/evolution
     *
     * Cria uma evolução para um atendimento.
     */
    public function store(StoreAttendanceEvolutionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = Attendance::with(['latestMedicalRegulation', 'sceneRecording'])
            ->where(function ($query) {
                $query->whereHas('latestMedicalRegulation')
                    ->orWhereHas('sceneRecording');
            })
            ->findOrFail($data['attendance_id']);

        switch ($data['form_type_slug']) {
            case getMorphAlias(MedicalRegulation::class):
                $result->latestMedicalRegulation->evolutions()->create([...$data, 'created_by' => auth()->user()->id]);
                break;
            case getMorphAlias(SceneRecording::class):
                $result->sceneRecording->evolutions()->create([...$data, 'created_by' => auth()->user()->id]);
                break;
        }

        return response()->json(['message' => 'Evolução cadastrada com sucesso.']);
    }
}
