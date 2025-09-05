<?php

namespace App\Http\Controllers;

use App\Enums\PeriodTypeEnum;
use Illuminate\Http\Request;
use App\Http\Requests\IndexUserScheduleRequest;
use App\Http\Resources\BaseScheduleSchemaResource;
use App\Http\Requests\UserSchedulesRequest;
use App\Models\UserSchedule;
use App\Models\Base;
use App\Models\User;
use App\Models\ScheduleEvent;
use App\Models\RegionalGroup;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserScheduleNewResource;
use App\Http\Resources\ScheduleBalanceHoursResource;
use App\Http\Requests\UpdateUserSchedulesRequest;
use App\Http\Requests\IndexUserScheduleReportsRequest;
use App\Http\Resources\BaseScheduleResource;
use App\Http\Resources\BaseScheduleReportsResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;


class UserScheduleController extends Controller
{
    private function applyPeriodTypeFilter($query, $data): void
    {
        $date = $data['date'] ?? null;
        if (!$date) return;
    
        $query->where(function ($query) use ($date) {
            $query->whereDate('prev_start_date', $date);
        });
    }

    private function convertToHoursMinutes($decimalHours): string
    {
        $isNegative = $decimalHours < 0;
        $absHours = abs($decimalHours);
    
        $hours = floor($absHours);
        $minutes = round(($absHours - $hours) * 60);
    
        if ($minutes === 60) {
            $hours += 1;
            $minutes = 0;
        }
    
        $formatted = sprintf('%02d:%02d', $hours, $minutes);
        return $isNegative ? '-' . $formatted : $formatted;
    }

    private function applyFilters($query, array $data)
    {
        return $query
            ->when(!empty($data['search']), fn ($q) => $q->whereHas('user', fn ($q) => $q->whereRaw('unaccent(users.name) ilike unaccent(?)', ["%{$data['search']}%"])
            )
            )
            ->when(!empty($data['event_ids']), fn ($q) => $q->whereHas('scheduleEvents', fn ($q) => $q->whereIn('schedule_event_type_id', $data['event_ids'])
            )
            )
            ->when(!empty($data['occupation_codes']), fn ($q) => $q->whereHas('user', fn ($q) => $q->whereIn('users.cbo', $data['occupation_codes'])
            )
            )
            ->when(!empty($data['regional_groups']), fn ($q) => $q->whereHas('base', fn ($q) => $q->whereIn('bases.regional_group_id', $data['regional_groups'])
            )
            )
            ->when(!empty($data['professional_ids']), fn ($q) => $q->whereIn('user_id', $data['professional_ids'])
            )
            ->when(!empty($data['links']), fn ($q) => $q->whereIn('link', $data['links'])
            )
            ->when(!empty($data['base_ids']), fn ($q) => $q->whereIn('base_id', $data['base_ids'])
            )
            ->when(!empty($data['start_date']) && !empty($data['end_date']), function ($q) use ($data) {
                $start = Carbon::parse($data['start_date'])->startOfDay();
                $end = Carbon::parse($data['end_date'])->endOfDay();
                $q->whereBetween('prev_start_date', [$start, $end]);
            });
    }

    private function calculateTotalPredictedHours($schedules)
    {
        return $schedules->reduce(function ($carry, $item) {
            if ($item->prev_start_date && $item->prev_end_date) {
                return $carry + Carbon::parse($item->prev_end_date)->floatDiffInHours(Carbon::parse($item->prev_start_date));
            }
<<<<<<< HEAD

            return $carry;
        }, 0);
    }

    private function calculateTotalWorkedHours($userSchedules): float
    {
        return $userSchedules->reduce(function ($carry, $schedule) {
            $valid = true;

            foreach ($schedule->scheduleEvents ?? [] as $evento) {
                if ($evento->schedule_event_type_id == 2 && $evento->schedule_event_type_id !== 3) {
                    $valid = false;
                    break;
                }
            }

            if ($valid && $schedule->userScheduleClocks) {
                foreach ($schedule->userScheduleClocks as $clock) {

                    if ($clock->clock_in && $clock->clock_out) {
                        $carry += Carbon::parse($clock->clock_out)
                            ->floatDiffInHours(Carbon::parse($clock->clock_in));
                    }
                }
            }
=======
>>>>>>> 86c150cf (rebase ajust)

            return $carry;
        }, 0);
    }

 private function calculateTotalWorkedHours($userSchedules): float
{
    return $userSchedules->reduce(function ($carry, $schedule) {
        $valid = true;

        foreach ($schedule->scheduleEvents ?? [] as $evento) {
            if ($evento->schedule_event_type_id == 2 && $evento->schedule_event_type_id !== 3) {
                $valid = false;
                break;
            }
        }

        if ($valid && $schedule->userScheduleClocks) {
            foreach ($schedule->userScheduleClocks as $clock) {

                if ($clock->clock_in && $clock->clock_out) {
                    $carry += Carbon::parse($clock->clock_out)
                        ->floatDiffInHours(Carbon::parse($clock->clock_in));
                }
            }
        }

        return $carry;
    }, 0);
}


 private function processChangeAndFoulEvents(
    $userSchedules,
    $user,
    &$substituteHours,
    &$justifiedAbsernceHours,
    &$convocateHours,
    &$substituteName,
    &$totalFouls,
    &$totalChanges
): void {
    foreach ($userSchedules as $schedule) {
        $clocks = $schedule->userScheduleClocks
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out');

        $clock = $clocks->first();

        if (!$clock) {
            continue;
        }

        $start = Carbon::parse($clock->clock_in);
        $end = Carbon::parse($clock->clock_out);
        $duration = $end->floatDiffInHours($start);

        foreach ($schedule->scheduleEvents ?? [] as $evento) {
            if (in_array($evento->schedule_event_type_id, [1, 2]) && $evento->reverse_professional_id == $user->id) {
                $substituteHours += $duration;
            }

            if ($evento->schedule_event_type_id == 2 && $evento->professional_id == $user->id) {
                $substituteName = optional($evento->reverseProfessional)->name;
            }

            if ($evento->schedule_event_type_id == 3 && $evento->professional_id == $user->id) {
                $justifiedAbsernceHours += $duration;
            }

            // Falta justificada
            if (
                $clocks->isEmpty() || 
                (!$clock->clock_in && !$clock->clock_out) ||
                ($evento->schedule_event_type_id == 2 && $evento->schedule_event_type_id !== 3)
            ) {
                $totalFouls++;
            }

            if ($evento->schedule_event_type_id == 1 && $evento->professional_id == $user->id) {
                $totalChanges++;
            }
        }
    }
}


    private function processPaidAndPendingHours($userId, &$paidHours, &$pendingHours): void
{
    $scheduleEvents = ScheduleEvent::with([
        'userSchedule.userScheduleClocks',
        'userScheduleReverseProfessional.userScheduleClocks'
    ])
        ->where('schedule_event_type_id', 1)
        ->where(fn($query) => $query->where('professional_id', $userId)
            ->orWhere('reverse_professional_id', $userId))
        ->get();

    foreach ($scheduleEvents as $evento) {
        if ($evento->reverse_professional_id === $userId) {
            $schedule = optional($evento->userSchedule);

            $clocks = $schedule->userScheduleClocks
                ->where('user_id', $userId)
                ->whereNotNull('clock_in')
                ->whereNotNull('clock_out');

            foreach ($clocks as $clock) {
                $paidHours += Carbon::parse($clock->clock_out)->floatDiffInHours(Carbon::parse($clock->clock_in));
            }
        }

        if ($evento->professional_id === $userId) {
            $originalHours = 0;
            $compensatedHours = 0;

            $scheduleOriginal = optional($evento->userSchedule);
            $originalClocks = $scheduleOriginal->userScheduleClocks
                ->where('user_id', $userId)
                ->whereNotNull('clock_in')
                ->whereNotNull('clock_out');

            foreach ($originalClocks as $clock) {
                $originalHours += Carbon::parse($clock->clock_out)->floatDiffInHours(Carbon::parse($clock->clock_in));
            }

            if ($evento->reverse_user_schedule_id) {
                $compensatedSchedule = optional($evento->userScheduleReverseProfessional);
                $compensatedClocks = $compensatedSchedule->userScheduleClocks
                    ->where('user_id', $evento->reverse_professional_id)
                    ->whereNotNull('clock_in')
                    ->whereNotNull('clock_out');

                foreach ($compensatedClocks as $clock) {
                    $compensatedHours += Carbon::parse($clock->clock_out)->floatDiffInHours(Carbon::parse($clock->clock_in));
                }
            }

            $paidHours += $compensatedHours;
            $pendingHours += max(0, $originalHours - $compensatedHours);
        }
    }
}


    /**
     * Cria novos horários para usuários (profissionais).
     *
     * @route POST /user-schedules
     */
    public function store(UserSchedulesRequest $request)
    {
        $validated = $request->validated();

        try {
            $result = DB::transaction(function () use ($validated) {
                $createdSchedules = [];

                foreach ($validated['professionals'] as $user) {
                    foreach ($validated['dates'] as $date) {
                        $conflictExists = UserSchedule::where('user_id', $user['id'])
                            ->where(function ($query) use ($date) {
                                $query->where(function ($q) use ($date) {
                                    $q->where('prev_end_date', '>', $date['prev_start_date'])
                                    ->where('prev_start_date', '<', $date['prev_end_date']);
                                });
                            })
                            ->exists();

                        if ($conflictExists) {
                            throw new \Exception('Conflito de horário detectado para o usuário selecionado!');
                        }

                        $schedule = UserSchedule::create([
                            'user_id'         => $user['id'],
                            'prev_start_date' => $date['prev_start_date'],
                            'prev_end_date'   => $date['prev_end_date'],
                            'urc_id'          => $validated['urc_id'],
                            'base_id'         => $validated['base_id'],
                            'link'            => $user['link'] ?? null,
                            'occupation_code' => $user['occupation_code'] ?? null,
                            'shift_id' => $date['shift_id'] ?? null,
                            'position_jobs_id' => $validated['position_jobs_id'] ?? null,
                            'regional_group_id' => $validated['regional_group_id'] ?? null,
                        ]);

                        $createdSchedules[] = $schedule;
                    }
                }

                return $createdSchedules;
            });

            return response()->json([
                'message' => 'Horários criados com sucesso.',
                'data' => UserScheduleNewResource::collection($result),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Erro ao criar horários.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lista os horários dos usuários com agrupamento por base e grupo regional.
     *
     * @route GET /user-schedules
     */

    public function index(IndexUserScheduleRequest $request): ResourceCollection
    {
        $data = $request->validated();
        $authUrcId = auth()->user()->urc_id;

        $userSchedules = UserSchedule::with([
            'user:id,name,phone,cbo',
            'user.occupation:code,name',
            'vehicle:id,code,urc_id,base_id',
            'base.regionalGroup',
            'regionalGroup',
            'scheduleEvents.reverseProfessional:id,name,cbo',
            'scheduleEvents.reverseProfessional.occupation:code,name',
            'reverseScheduleEvents.professional:id,name,cbo',
            'reverseScheduleEvents.professional.occupation:code,name',
            'reverseScheduleEvents.reverseProfessional:id,name,cbo',
            'reverseScheduleEvents.reverseProfessional.occupation:code,name',
            'userScheduleClocks',
            'positionJobs'
        ])
            ->where('urc_id', $authUrcId)
            ->when(!empty($data['search']), function ($query) use ($data) {
                $query->whereHas('user', function ($q) use ($data) {
                    $q->whereRaw('unaccent(users.name) ilike unaccent(?)', ["%{$data['search']}%"]);
                });
            })
            ->when(!empty($data['occupation_codes']), function ($query) use ($data) {
                $query->whereHas('user', function ($q) use ($data) {
                    $q->whereIn('users.cbo', $data['occupation_codes']);
                });
            })
            ->when(!empty($data['regional_groups']), function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->whereHas('base', function ($baseQuery) use ($data) {
                        $baseQuery->whereIn('bases.regional_group_id', $data['regional_groups']);
                    })->orWhereIn('user_schedules.regional_group_id', $data['regional_groups']);
                });
            });

        $this->applyPeriodTypeFilter($userSchedules, $data);

        $userSchedules = $userSchedules
            ->orderByDesc('prev_start_date')
            ->get();

        $groupedByRegionalGroup = $userSchedules->groupBy(function ($item) {
            if ($item->base && $item->base->regionalGroup) {
                return $item->base->regionalGroup->id;
            }
            
            if ($item->regional_group_id) {
                return $item->regional_group_id;
            }
            
            return 'null_group';
        });
    
        $results = collect();
        
        $groupedByRegionalGroup->each(function ($schedules, $regionalGroupId) use (&$results) {
            $regionalGroup = $regionalGroupId !== 'null_group'
                ? RegionalGroup::find($regionalGroupId)
                : null;
    
            $basesGrouped = $schedules->groupBy('base_id')->map(function ($baseSchedules, $baseId) {
                $base = $baseId ? Base::with(['vehicles:id,code,urc_id,base_id', 'regionalGroup'])->find($baseId) : null;
    
                return [
                    'base' => $base,
                    'vehicles' => $base ? $base->vehicles : [],
                    'user_schedules' => $baseSchedules->sortBy([
                        ['prev_start_date', 'asc'],
                        ['user.name', 'asc'],
                    ])->values(),
                ];
            });
    
            $results->push([
                'regional_group' => $regionalGroup,
                'bases' => $basesGrouped->values(),
            ]);
        });
        
        $groupedByRegionalGroup->each(function ($schedules, $regionalGroupId) use (&$results, $authUrcId) {
            $regionalGroup = $regionalGroupId !== 'null_group'
                ? RegionalGroup::find($regionalGroupId)
                : null;
                
            if ($regionalGroup) {
                $basesWithoutSchedules = Base::with(['vehicles:id,code,urc_id,base_id'])
                    ->where('urc_id', $authUrcId)
                    ->where('regional_group_id', $regionalGroup->id)
                    ->whereNotIn('id', $schedules->pluck('base_id')->filter())
                    ->get();
                    
                if ($basesWithoutSchedules->isNotEmpty()) {
                    $basesGrouped = $basesWithoutSchedules->map(function ($base) {
                        return [
                            'base' => $base,
                            'vehicles' => $base->vehicles,
                            'user_schedules' => collect(),
                        ];
                    });
                    
                    $results->push([
                        'regional_group' => $regionalGroup,
                        'bases' => $basesGrouped->values(),
                    ]);
                }
            }
        });
        
        if ($groupedByRegionalGroup->has('null_group')) {
            $nullGroupSchedules = $groupedByRegionalGroup->get('null_group');
            $basesWithoutSchedules = Base::with(['vehicles:id,code,urc_id,base_id'])
                ->where('urc_id', $authUrcId)
                ->whereNull('regional_group_id')
                ->whereNotIn('id', $nullGroupSchedules->pluck('base_id')->filter())
                ->get();
                
            if ($basesWithoutSchedules->isNotEmpty()) {
                $basesGrouped = $basesWithoutSchedules->map(function ($base) {
                    return [
                        'base' => $base,
                        'vehicles' => $base->vehicles,
                        'user_schedules' => collect(),
                    ];
                });
                
                $results->push([
                    'regional_group' => null,
                    'bases' => $basesGrouped->values(),
                ]);
            }
        } else {
            $basesWithoutRegionalGroup = Base::with(['vehicles:id,code,urc_id,base_id'])
                ->where('urc_id', $authUrcId)
                ->whereNull('regional_group_id')
                ->get();
                
            if ($basesWithoutRegionalGroup->isNotEmpty()) {
                $basesGrouped = $basesWithoutRegionalGroup->map(function ($base) {
                    return [
                        'base' => $base,
                        'vehicles' => $base->vehicles,
                        'user_schedules' => collect(),
                    ];
                });
                
                $results->push([
                    'regional_group' => null,
                    'bases' => $basesGrouped->values(),
                ]);
            }
        }
    
        $includedBaseIds = $userSchedules->pluck('base_id')->unique();
        
        $hasNullRegionalGroup = $results->contains(function ($item) {
            return $item['regional_group'] === null;
        });
    
        $allBases = Base::with(['vehicles:id,code,urc_id,base_id', 'regionalGroup'])
            ->where('urc_id', $authUrcId)
            ->whereNotIn('id', $includedBaseIds)
            ->get();
    
        $additionalGrouped = $allBases->groupBy(function ($base) {
            return optional($base->regionalGroup)->id ?? 'null_group';
        })->map(function ($bases, $regionalGroupId) use ($hasNullRegionalGroup) {
            if ($regionalGroupId === 'null_group' && $hasNullRegionalGroup) {
                return null;
            }
            
            $basesMapped = $bases->map(function ($base) {
                return [
                    'base' => $base,
                    'vehicles' => $base->vehicles,
                    'user_schedules' => collect(),
                ];
            });
    
            $regionalGroup = $regionalGroupId !== 'null_group'
                ? RegionalGroup::find($regionalGroupId)
                : null;
    
            return [
                'regional_group' => $regionalGroup,
                'bases' => $basesMapped->values(),
            ];
        })->filter();
    
        $includedRegionalGroupIds = $results->pluck('regional_group.id')->filter()->unique()
            ->concat($additionalGrouped->pluck('regional_group.id')->filter()->unique())
            ->unique();
        
        $allRegionalGroups = RegionalGroup::where(function ($query) use ($authUrcId) {
            $query->whereHas('bases', function ($baseQuery) use ($authUrcId) {
                $baseQuery->where('urc_id', $authUrcId);
            })->orWhereDoesntHave('bases');
        })->whereNotIn('id', $includedRegionalGroupIds)
        ->get();
        
        $emptyRegionalGroups = $allRegionalGroups->map(function ($regionalGroup) use ($authUrcId) {
            $regionalBases = Base::with(['vehicles:id,code,urc_id,base_id'])
                ->where('urc_id', $authUrcId)
                ->where('regional_group_id', $regionalGroup->id)
                ->get();
                
            $basesMapped = $regionalBases->map(function ($base) {
                return [
                    'base' => $base,
                    'vehicles' => $base->vehicles,
                    'user_schedules' => collect(),
                ];
            });
            
            return [
                'regional_group' => $regionalGroup,
                'bases' => $basesMapped->values(),
            ];
        });
        
        $mergedResults = $results->concat($additionalGrouped->values())->concat($emptyRegionalGroups);
    
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageResults = $mergedResults->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedResults = new LengthAwarePaginator(
            $currentPageResults,
            $mergedResults->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
    
        return BaseScheduleResource::collection($paginatedResults);
    }

    /**
     * Atualiza um horário de usuário específico.
     *
     * @route PUT /user-schedules/{id}
     */

    public function update(UpdateUserSchedulesRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();
        $userSchedule = UserSchedule::findOrFail($id);
        $userSchedule->update($validated);

        return response()->json(new UserScheduleNewResource($userSchedule));
    }

    /**
     * Lista os usuários que possuem horários após uma data específica.
     *
     * @route GET /user-schedules/users
     */

    public function getUsers(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $authUrcId = auth()->user()->urc_id;
        $baseDate = Carbon::parse($validated['date']);

        $userSchedules = UserSchedule::with(['user:id,name,phone,cbo', 'user.occupation:code,name'])
            ->where('urc_id', $authUrcId)
            ->whereDate('prev_start_date', '>', $baseDate)
            ->get()
            ->groupBy('user_id')
            ->map(function ($schedules) {
                $firstSchedule = $schedules->first();

                return [
                    'user_schedule_id' => $firstSchedule->id,
                    'user' => $firstSchedule->user,
                ];
            })
            ->values();

        return response()->json([
            'data' => $userSchedules,
        ]);
    }

    /**
     * Remove um horário de usuário específico.
     *
     * @route DELETE /user-schedules/{id}
     */

    public function destroy(string $id): JsonResponse
    {
        $result = UserSchedule::findOrFail($id);
        $result->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function reportSchedule(IndexUserScheduleReportsRequest $request): ResourceCollection
    {
        $data = $request->validated();
        $authUrcId = auth()->user()->urc_id;

        $userSchedules = UserSchedule::with([
            'user:id,name,phone,cbo',
            'user.occupation:code,name',
            'vehicle:id,code,urc_id,base_id',
            'base.regionalGroup',
            'scheduleEvents.reverseProfessional:id,name,cbo',
            'scheduleEvents.reverseProfessional.occupation:code,name',
            'reverseScheduleEvents.professional:id,name,cbo',
            'reverseScheduleEvents.professional.occupation:code,name',
            'urc',
            'userScheduleClocks'

        ])
            ->where('urc_id', $authUrcId)
            ->when(!empty($data['search']), function ($query) use ($data) {
                $query->whereHas('user', function ($q) use ($data) {
                    $q->whereRaw('unaccent(users.name) ilike unaccent(?)', ["%{$data['search']}%"]);
                });
            })
            ->when(!empty($data['event_ids']), function ($query) use ($data) {
                $query->whereHas('scheduleEvents', function ($q) use ($data) {
                    $q->whereIn('schedule_event_type_id', $data['event_ids']);
                });
            })
            ->when(!empty($data['occupation_codes']), function ($query) use ($data) {
                $query->whereHas('user', function ($q) use ($data) {
                    $q->whereIn('users.cbo', $data['occupation_codes']);
                });
            })
            ->when(!empty($data['regional_groups']), function ($query) use ($data) {
                $query->whereHas('base', function ($q) use ($data) {
                    $q->whereIn('bases.regional_group_id', $data['regional_groups']);
                });
            })
            ->when(!empty($data['professional_ids']), function ($query) use ($data) {
                $query->whereIn('user_id', $data['professional_ids']);
            })
            ->when(!empty($data['links']), function ($query) use ($data) {
                $query->whereIn('link', $data['links']);
            })
            ->when(!empty($data['base_ids']), function ($query) use ($data) {
                $query->whereIn('base_id', $data['base_ids']);
            })->when(!empty($data['start_date']) && !empty($data['end_date']), function ($query) use ($data) {
            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = Carbon::parse($data['end_date'])->endOfDay();

            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('prev_start_date', [$start, $end])
                  ->orWhereHas('userScheduleClocks', function ($q2) use ($start, $end) {
                      $q2->whereBetween('clock_in', [$start, $end])
                         ->orWhereBetween('clock_out', [$start, $end]);
                  });
            });
        });

        $userSchedules = $userSchedules
        ->orderBy('prev_start_date')
        ->orderBy(User::select('name')
            ->whereColumn('users.id', 'user_schedules.user_id')
            ->limit(1))
        ->get();
    
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageResults = $userSchedules->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedResults = new LengthAwarePaginator(
            $currentPageResults,
            $userSchedules->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
        return BaseScheduleReportsResource::collection($paginatedResults);
    }

    /**
     * Lista o banco de horas com informações gerais.
     *
     * @route GET /balance-hours
     */

    public function balanceHoursSchedule(IndexUserScheduleReportsRequest $request): ResourceCollection
{
    $data = $request->validated();
    $authUrcId = auth()->user()->urc_id;

        $schedules = $this->applyFilters(
            UserSchedule::with([
                'user:id,name,phone,cbo',
                'user.occupation:code,name',
                'base:id,name,regional_group_id',
                'base.regionalGroup:id,name',
                'scheduleEvents' => fn ($query) => $query->whereIn('schedule_event_type_id', [1, 2, 3]),
                'scheduleEvents.reverseProfessional:id,name',
                'reverseScheduleEvents',
                'userScheduleClocks'
            ])->where('urc_id', $authUrcId),
            $data
        )->get();

$groupedByRegional = $schedules->groupBy(function ($item) {
    return optional(optional($item->base)->regionalGroup)->id ?? 'sem_grupo';
});

    $result = $groupedByRegional->map(function ($schedulesInGroup, $regionalGroupId) use ($data) {
        $regionalGroup = $regionalGroupId !== 'sem_grupo' ? optional($schedulesInGroup->first()->base->regionalGroup) : null;

        $bases = $schedulesInGroup->groupBy('base_id')->map(function ($baseSchedules) use ($data) {
            $firstSchedule = $baseSchedules->first();
            $base = optional($firstSchedule)->base;

            $users = $baseSchedules->groupBy('user_id')->map(function ($userSchedules) use ($data) {
                $user = optional($userSchedules->first())->user;

                if (!$user) {
                    return null;
                }

                $totalPredicted = $this->calculateTotalPredictedHours($userSchedules);
                $totalWorked = $this->calculateTotalWorkedHours($userSchedules);

                    $convocateHours = 0;
                    $substituteHours = 0;
                    $justifiedAbsernceHours = 0;
                    $substituteName = '';
                    $substituteCbo = null;
                    $substituteCboName = null;
                    $totalFouls = 0;
                    $totalConvocates = 0;
                    $totalChanges = 0;
                    $paidHours = 0;
                    $pendingHours = 0;

                    $eventosConvocacoes = ScheduleEvent::where('schedule_event_type_id', 2)
    ->where('reverse_professional_id', $user->id)
    ->whereNotNull('user_schedule_id')
    ->whereHas('userSchedule.userScheduleClocks', function ($query) use ($data) {
        $query->whereNotNull('clock_in')
              ->whereNotNull('clock_out')
              ->whereDate('clock_in', '>=', $data['start_date'])
              ->whereDate('clock_out', '<=', $data['end_date']);
    })
    ->with('userSchedule.userScheduleClocks')
    ->get();
foreach ($eventosConvocacoes as $evento) {
                    foreach ($evento->userSchedule->userScheduleClocks ?? [] as $clock) {

    if ($clock->clock_in && $clock->clock_out) {
        $convocateHours += Carbon::parse($clock->clock_out)
            ->floatDiffInHours(Carbon::parse($clock->clock_in));
      
             $totalConvocates++;
    }
}
  }


                    $this->processChangeAndFoulEvents($userSchedules, $user, $substituteHours, $justifiedAbsernceHours, $convocateHours, $substituteName, $totalFouls, $totalChanges, $substituteCbo, $substituteCboName);

                $this->processPaidAndPendingHours($user->id, $paidHours, $pendingHours);

                    return [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'cbo' => $user->cbo,
                            'cbo_name' => optional($user->occupation)->name,
                        ],
                        'total_predicted_hours' => $this->convertToHoursMinutes($totalPredicted),
                        'total_worked_hours' => $this->convertToHoursMinutes($totalWorked),
                        'total_balance' => $this->convertToHoursMinutes($totalWorked - $totalPredicted + $substituteHours + $justifiedAbsernceHours + $convocateHours + ($paidHours - $pendingHours)),
                        'foul_justificate' => $this->convertToHoursMinutes($justifiedAbsernceHours),
                        'convocacoes' => $this->convertToHoursMinutes($convocateHours),
                        'reverseProfessionalName' => $substituteName,
                        'reverseProfessionalCbo' => $substituteCbo,
                        'reverseProfessionalCboName' => $substituteCboName,
                        'total_fouls' => $totalFouls,
                        'total_convocates' => $totalConvocates,
                        'total_changes' => $totalChanges,
                        'convocation_hours' => $this->convertToHoursMinutes($convocateHours),
                        'fouls_hours_paid' => $this->convertToHoursMinutes($paidHours),
                        'fouls_hours_pending' => $this->convertToHoursMinutes($pendingHours),
                    ];
                })->filter()->values();

            return [
                'base' => [
                    'id' => optional($base)->id,
                    'name' => optional($base)->name ?? 'Sem base',
                ],
                'users' => $users,
            ];
        });

        return [
            'regional_group' => $regionalGroup
                ? ['id' => $regionalGroup->id, 'name' => $regionalGroup->name]
                : ['id' => null, 'name' => 'Sem grupo'],
            'bases' => $bases->values(),
        ];
    });

    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $perPage = 10;

    $currentPageResults = $result->slice(($currentPage - 1) * $perPage, $perPage)->values();

    $paginatedResults = new LengthAwarePaginator(
        $currentPageResults,
        $result->count(),
        $perPage,
        $currentPage,
        ['path' => Paginator::resolveCurrentPath()]
    );

    return ScheduleBalanceHoursResource::collection($paginatedResults);
}


}
