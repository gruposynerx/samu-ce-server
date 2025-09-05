<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLogRequest;
use App\Http\Resources\UserLogResource;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Histórico de acesso', description: 'Seção responsável por guardar o histórico de acesso do usuário.')]
class UserLogController extends Controller
{
    /**
     * GET api/user-logs
     *
     * Retorna uma lista paginada de histórico de acesso.
     */
    public function index(UserLogRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = UserLog::with(['urgencyRegulationCenter', 'role'])
            ->when(!empty($data['search']), function ($query) use ($data) {
                $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%");
            })
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->whereBetween('logged_at', [
                    Carbon::create($data['start_date'])->startOfDay(),
                    Carbon::create($data['end_date'])->endOfDay(),
                ]);
            })
            ->paginate(10);

        return UserLogResource::collection($results);
    }
}
