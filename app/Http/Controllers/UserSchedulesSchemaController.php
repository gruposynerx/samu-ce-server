<?php

namespace App\Http\Controllers;

use App\Enums\PeriodTypeEnum;
use App\Http\Requests\IndexUserScheduleRequest;
use App\Http\Requests\UserSchedulesSchemaRequest;
use App\Http\Resources\BaseScheduleSchemaResource;
use App\Http\Resources\UserScheduleSchemaResource;
use App\Models\Base;
use App\Models\UrgencyRegulationCenter;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\UserScheduleSchema;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Escala', description: 'Gestão de escalas')]
class UserSchedulesSchemaController extends Controller
{
    private function applyPeriodTypeFilter($query, $data): void
    {
        $dayOfWeek = (string) Carbon::parse($data['date'])->dayOfWeek;
        $periodType = (int) $data['period_type'];

        $query->where('valid_from', '<=', $data['date'])
            ->where('valid_through', '>=', $data['date'])
            ->whereJsonContains('days_of_week', $dayOfWeek)
            ->where(function ($query) use ($periodType) {
                if ($periodType === PeriodTypeEnum::DAYTIME->value) {
                    $query->where('clock_in', '<', '19:00')
                          ->where('clock_out', '>=', '07:00');
                } else {
                    $query->where(function ($query) {
                        $query->where(function ($q) {
                            $q->where('clock_in', '>=', '19:00')
                              ->orWhere('clock_out', '<', '07:00');
                        })
                        ->orWhere(function ($q) {
                            $q->where('clock_in', '<', '19:00')
                              ->where('clock_out', '>', '19:00');
                        });
                    });
                }
            });
            
    }

    /**
     * GET api/schedules-schema
     *
     * Retorna uma lista página de escalas.
     */
    public function index(IndexUserScheduleRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = Base::with([
            'vehicleType',
            'city:cities.id,cities.name',
            'schedulesSchemas' => function ($query) use ($data) {
                $this->applyPeriodTypeFilter($query, $data);
            },
            'regionalGroup',
            'vehicles:vehicles.id,vehicles.urc_id,vehicles.code,vehicles.base_id',
            'scheduledUsers:users.id,users.name,users.phone,users.cbo',
        ])
            ->join('cities', function ($join) {
                $join->on('bases.city_id', '=', 'cities.id')->select('cities.id', 'cities.name');
            })
            ->when(!empty($data['search']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->whereRaw('unaccent(bases.name) ilike unaccent(?)', "%{$data['search']}%")
                        ->orWhereHas('scheduledUsers', function ($query) use ($data) {
                            $query->whereRaw('unaccent(users.name) ilike unaccent(?)', "%{$data['search']}%");
                        })
                        ->orWhereHas('vehicles', function ($query) use ($data) {
                            $query->whereRaw('unaccent(vehicles.code) ilike unaccent(?)', "%{$data['search']}%");
                        });
                });
            })
            ->when(!empty($data['regional_groups']), function ($query) use ($data) {
                $query->whereIn('regional_group_id', $data['regional_groups']);
            })
            ->whereHas('schedulesSchemas', function ($query) use ($data) {
                $this->applyPeriodTypeFilter($query, $data);
            })
            ->whereHas('regionalGroup')
            ->orderBy('bases.regional_group_id')
            ->orderBy('cities.name')
            ->select('bases.*')
            ->get();

        $flattenedResults = $results->flatMap(function ($base) {
            $prepareScheduledUsers = function ($base) {
                $scheduleSchemasUserIds = $base->schedulesSchemas->pluck('user_id')->unique();

                return $base->scheduledUsers->filter(fn ($user) => $scheduleSchemasUserIds->contains($user->id))->values();
            };

            if ($base->vehicles->isEmpty()) {
                $newBase = $base->replicate();
                $newBase->id = $base->id;
                $newBase->setRelation('vehicles', collect([]));
                $newBase->setRelation('scheduledUsers', $prepareScheduledUsers($base));

                return collect([$newBase]);
            }

            return $base->vehicles->map(function ($vehicle) use ($base, $prepareScheduledUsers) {
                $newBase = $base->replicate();
                $newBase->id = $base->id;
                $newBase->setRelation('vehicles', collect([$vehicle]));
                $newBase->setRelation('scheduledUsers', $prepareScheduledUsers($base));

                return $newBase;
            });
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageResults = $flattenedResults->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedResults = new LengthAwarePaginator(
            $currentPageResults,
            $flattenedResults->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return BaseScheduleSchemaResource::collection($paginatedResults);
    }

    /**
     * GET api/schedules-schema/{id}
     *
     * Retorna uma escala.
     *
     * @urlParam id string required ID da escala.
     */
    public function show(string $id): JsonResponse
    {
        $result = UserScheduleSchema::with('schedulable:id,name')->findOrFail($id);

        return response()->json(new UserScheduleSchemaResource($result));
    }

    /**
     * POST api/schedules-schema
     *
     * Realiza o cadastro de uma escala.
     */
    public function store(UserSchedulesSchemaRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['clock_out'] === '19:00') {
            $data['clock_out'] = '18:59';
        } elseif ($data['clock_out'] === '07:00') {
            $data['clock_out'] = '06:59';
        }

        $result = DB::transaction(function () use ($data) {
            $workplace = Base::find($data['schedulable_id']) ?? UrgencyRegulationCenter::find($data['schedulable_id']);

            $result = $workplace->schedulesSchemas()->create([
                ...$data,
                'schedulable_type' => app($workplace::class)->getMorphClass(),
            ]);

            $this->createUserSchedules($data, $result);

            return $result;
        });

        $result->load('schedules');

        return response()->json(new UserScheduleSchemaResource($result));
    }

    /**
     * PUT api/schedules-schema/{id}
     *
     * Atualiza uma escala.
     *
     * @urlParam id string required ID da escala.
     */
    public function update(UserSchedulesSchemaRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($data, $id) {
            $currentSchedules = UserSchedule::where('schema_id', $id)->get();

            foreach ($currentSchedules as $schedule) {
                $schedule->delete();
            }

            $workplace = Base::find($data['schedulable_id']) ?? UrgencyRegulationCenter::find($data['schedulable_id']);

            $result = UserScheduleSchema::findOrFail($id);

            $result->update([
                ...$data,
                'schedulable_type' => app($workplace::class)->getMorphClass(),
                'schedulable_id' => $workplace->id,
            ]);

            $result->refresh();

            $workplace->schedulesSchemas()->save($result);

            $this->createUserSchedules($data, $result);

            return $result;
        });

        $result->load('schedules');

        return response()->json(new UserScheduleSchemaResource($result));
    }

    private function createUserSchedules(array $data, $result): void
    {
        $workDays = CarbonPeriod::between($data['valid_from'], $data['valid_through'])
            ->filter(fn ($date) => in_array($date->dayOfWeek, $data['days_of_week']));

        $query = User::query()->select('id');

        foreach ($workDays as $workDay) {
            $date = $workDay->toDateString();
            $startsAt = Carbon::parse("$date $result->clock_in");
            $endsAt = Carbon::parse("$date $result->clock_out");

            $query->whereHas('schedules', function ($query) use ($startsAt, $endsAt, $result) {
                $query->where('user_id', $result->user_id)
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
            });
        }

        $conflicts = $query->count();

        if ($conflicts) {
            throw ValidationException::withMessages(['conflict' => 'O profissional já possui escala cadastrada nesse horário.']);
        }

        foreach ($workDays as $workDay) {
            $date = $workDay->toDateString();
            $clockIn = $result->clock_in;
            $clockOut = $result->clock_out;
            $startsAt = Carbon::parse("$date $result->clock_in");
            $endsAt = null;

            if ($clockIn > $clockOut) {
                $endsAt = Carbon::parse("$date $result->clock_out")->addDay();
            } else {
                $endsAt = Carbon::parse("$date $result->clock_out");
            }

            UserSchedule::create([
                'user_id' => $result->user_id,
                'schema_id' => $result->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'occupation_code' => $result->user->cbo,
            ]);
        }
    }
}
