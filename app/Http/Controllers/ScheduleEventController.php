<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleEvent;
use App\Models\ScheduleEventType;
use App\Http\Requests\ScheduleEventRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ScheduleEventTypesResource;
use App\Http\Resources\ScheduleEventResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;


class ScheduleEventController extends Controller
{
    public function store(ScheduleEventRequest $request): JsonResponse
    {
        $data = $request->validated();

        $exists = ScheduleEvent::where(function ($query) use ($data) {
            $query->where('user_schedule_id', $data['user_schedule_id'])
                  ->orWhere('reverse_user_schedule_id', $data['user_schedule_id']);
        
            if (!empty($data['reverse_user_schedule_id'])) {
                $query->orWhere('user_schedule_id', $data['reverse_user_schedule_id'])
                      ->orWhere('reverse_user_schedule_id', $data['reverse_user_schedule_id']);
            }
        })
        ->where('schedule_event_type_id', 1)
        ->exists();
        

        
        if ($data['schedule_event_type_id'] == 1 && $exists) {
            return response()->json([
                'message' => 'Já existe um evento de Troca para essa escala.',
            ], 422);
        }

        $existsConvocation = ScheduleEvent::where(function ($query) use ($data) {
            $query->where('user_schedule_id', $data['user_schedule_id'])
                  ->orWhere('reverse_user_schedule_id', $data['user_schedule_id']);
        })
        ->where('schedule_event_type_id', 2)
        ->exists();

      if ( $existsConvocation) {
            return response()->json([
                'message' => 'Esse profissinal já passui um evento de convocação, não pode ser atribuido outros eventos para essa escala.',
            ], 422);
        }


        $result = ScheduleEvent::create($data);

        return response()->json($result);
    }

    public function listTypes()
    {
        $result = ScheduleEventType::paginate();
        return ScheduleEventTypesResource::collection($result);
    }

    public function findEventsByReverseUserId(string $id): JsonResponse
    {
        $result = ScheduleEvent::where('reverse_professional_id',$id)
        ->where('reverse_user_schedule_id', null)
        ->with('userSchedule')
        ->with('professional')
        ->paginate(10);

        $result->getCollection()->transform(function ($item) {
            if ($item->userSchedule && $item->userSchedule->prev_start_date && $item->userSchedule->prev_end_date) {
                $start = Carbon::parse($item->userSchedule->prev_start_date);
                $end = Carbon::parse($item->userSchedule->prev_end_date);
                $diffInHours = $start->diffInMinutes($end) / 60;
                $item->userSchedule->diff_hours = $diffInHours;
            } else {
                $item->userSchedule->diff_hours = null;
            }
            return $item;
        });
        return response()->json($result);
    }

    public function update(ScheduleEventRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $result = ScheduleEvent::findOrFail($id);
        $result->update($data);

        return response()->json(new ScheduleEventResource($result));
    }
}