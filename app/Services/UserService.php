<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createUser(array $data): JsonResponse
    {
        $profiles = collect($data['profiles'])->map(function ($profile) {
            return (object) $profile;
        })->groupBy('urc_id');

        if ($data['mobile_access']) {
            $data['last_modified_mobile_access_user_id'] = auth()->user()->id;
        }

        $result = User::create($data);

        $profiles->map(function ($profile, $urcId) use ($result) {
            $result->assignRole($profile->pluck('role_id')->toArray(), $urcId);
        });

        return response()->json(new UserResource($result));
    }

    public function getUsers(string $search, $getProfessionals = false, $cbo = null, $roleIds = null): LengthAwarePaginator
    {
        $professionalsCodes = ['422205', '322230', '322205', '223505', '515135'];
        $clinicalDoctorCode = '2251';

        $query = User::query();

        $query->with([
            'city',
            'city.federalUnit',
            'urcRoles',
            'lastPasswordHistory',
            'urgencyRegulationCenters.userRoles',
            'occupation',
            'latestStatusesHistory:user_status_histories.id,user_status_histories.user_id,user_status_histories.status_id',
        ]);

        if ($getProfessionals) {
            $query->with(['schedulesSchemas']);
        }

        $query->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->when($search ?? null, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%")
                        ->orWhereRaw('unaccent(identifier) ilike unaccent(?)', "%{$search}%");
                });
            })
            ->when($getProfessionals, function ($query) use ($professionalsCodes, $clinicalDoctorCode) {
                $query->where(function ($query) use ($professionalsCodes, $clinicalDoctorCode) {
                    $query->whereIn('cbo', $professionalsCodes)
                        ->orWhere('cbo', 'like', "$clinicalDoctorCode%");
                });
            })
            ->when($cbo, function ($query) use ($cbo) {
                $query->where('cbo', $cbo);
            })
            ->when($roleIds, function ($query) use ($roleIds) {
                $roleIdsArray = explode(',', $roleIds);

                foreach ($roleIdsArray as $roleId) {
                    $query->whereExists(function ($subquery) use ($roleId) {
                        $subquery->select(DB::raw(1))
                            ->from('user_roles')
                            ->whereRaw('user_roles.user_id = users.id')
                            ->where('user_roles.role_id', $roleId);
                    });
                }
            })
            ->select('users.*')
            ->distinct('users.id')
            ->orderByRaw('users.id, users.created_at ASC');

        $results = $query->paginate(10);

        $results->through(function (User $user) {
            $user->urgencyRegulationCenters->each(function ($urc) use ($user) {
                $urc->userRoles = $urc->userRoles->filter(function ($role) use ($user) {
                    return $role->pivot->user_id === $user->id;
                })->toArray();

                return $urc;
            });

            $user->urcRoles = $user->urcRoles->filter(function ($role) {
                return $role->pivot->urc_id === auth()->user()->urc_id;
            });

            return $user;
        });

        return $results;
    }
}
