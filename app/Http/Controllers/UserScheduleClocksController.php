<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateOrUpdateUserScheduleClocksRequest;
use App\Models\UserScheduleClocks;
use App\Http\Resources\UserScheduleClocksResource;

class UserScheduleClocksController extends Controller
{
    public function registerPoint(CreateOrUpdateUserScheduleClocksRequest $request)
    {
        $validated = $request->validated();

        $record = UserScheduleClocks::where('user_id', $validated['user_id'])
            ->where('user_schedule_id', $validated['user_schedule_id'])
            ->first();

        if ($record) {

            $record->update([
                'clock_in' => $validated['clock_in'] ?? $record->clock_in,
                'clock_out' => $validated['clock_out'] ?? $record->clock_out,
            ]);

            return response()->json([
                'message' => 'Registro atualizado com sucesso.',
                'data' => $record
            ]);
        }

        $newRecord = UserScheduleClocks::create($validated);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'data' => $newRecord,
        ]);
    }
}
