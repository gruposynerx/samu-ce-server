<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchRolesByUnitAndUserRequest;
use App\Http\Requests\UpdateProfileUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Authentication', 'Endpoints for authentication')]
#[Subgroup('User Roles', 'Endpoints for manage user roles')]
class UserRoleController extends Controller
{
    /**
     * GET /api/user/roles-by-unit
     *
     * Fetch the roles that the user has access to in the selected unit.
     *
     * @unauthenticated
     */
    public function fetchRolesByUnitAndUser(FetchRolesByUnitAndUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $avaliableRoles = Role::select(['id', 'name'])->whereHas('avaliableFromUnit', fn ($q) => $q->where([
            ['user_id', $data['user_id']],
            ['urc_id', $data['urc_id']],
        ]))->get();

        return response()->json($avaliableRoles);
    }

    /**
     * PUT api/change-profile
     *
     * Altera o perfil do usuário logado.
     */
    public function changeProfile(UpdateProfileUserRequest $request): JsonResponse
    {
        $result = auth()->user();
        $result?->update($request->validated());

        $result->hasPowerBI = $result->powerBIReports->isNotEmpty();

        return response()->json(
            new UserResource(Auth::user()?->load([
                'currentRole',
                'city.federalUnit',
                'urcRoles',
                'currentUrgencyRegulationCenter:urgency_regulation_centers.id,urgency_regulation_centers.name',
                'urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
                'urgencyRegulationCenters.userRoles' => function ($query) use ($result) {
                    $query->where('user_id', $result?->id);
                },
                'place',
            ]))
        );
    }
}
