<?php

namespace App\Http\Controllers;

use App\Models\SceneRecording;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Registro de Cena', description: 'Seção responsável pela gestão do Registro de Cena')]
class AttendanceSceneRecordingController extends Controller
{
    /**
     * GET api/scene-recording/identifiers/{attendance_id}
     *
     * Retorna uma página de registros de cena do atendimento.
     *
     * @urlParam attendance_id string required ID do atendimento.
     */
    public function identifiers(string $attendanceId): ResourceCollection
    {
        $results = SceneRecording::select('id')->where('attendance_id', $attendanceId)->orderBy('created_at')->paginate(20);

        return JsonResource::collection($results);
    }
}
