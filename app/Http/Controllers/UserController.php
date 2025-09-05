<?php

namespace App\Http\Controllers;

use App\Http\Requests\DutyReportAbleProfessionalsRequest;
use App\Http\Requests\IndexUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\CadsusService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Usuário', description: 'Gestão de usuários')]
class UserController extends Controller
{
    /**
     * GET api/users
     *
     * Retorna as informações de todos os usuários que possuam cadastro na unidade base do usuário logado.
     */
    public function index(IndexUserRequest $request): ResourceCollection
    {
        $search = $request->validated('search');
        $cbo = $request->validated('cbo');
        $roleIds = $request->validated('role_ids');

        $results = (new UserService())->getUsers($search ?? '', false, $cbo, $roleIds);

        return UserResource::collection($results);
    }

    /**
     * GET api/users/{id}
     *
     * Retorna todas as informações do usuário, por id (apenas se a unidade base desse usuário for a mesma do usuário logado).
     *
     * @urlParam id string required ID do usuário.
     */
    public function show(string $id): JsonResponse
    {
        $result = User::with([
            'city',
            'city.federalUnit',
            'urcRoles' => fn ($q) => $q->where('urc_id', auth()->user()->urc_id),
            'urcRoles.urgencyRegulationCenter:urgency_regulation_centers.id,urgency_regulation_centers.name',
        ])
            ->whereHas('urcRoles', function ($query) {
                $query->where('urc_id', auth()->user()->urc_id);
            })
            ->findOrFail($id);

        return response()->json(new UserResource($result));
    }

    /**
     * POST api/users
     *
     * Realiza o cadastro de um usuário.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = (new UserService())->createUser($data);

        return response()->json($result);
    }

    /**
     * PUT api/users/{id}
     *
     * Realiza a atualização dos dados de um usuário.
     *
     * @urlParam id string required ID do usuário.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $profiles = collect($data['profiles'])->map(function ($profile) {
            return (object) $profile;
        })->groupBy('urc_id');

        // $removedLoggedRole = $profiles->get(auth()->user()->urc_id)?->filter(function ($profile) {
        //     return $profile->role_id === auth()->user()->current_role;
        // })->isEmpty() ?? true;

        // if ($removedLoggedRole) {
        //     throw ValidationException::withMessages(['profiles' => 'Não é possível remover seu perfil estando logado na CRU.']);
        // }

        $result = User::findOrFail($id);
        $mobileAccess = $data['mobile_access'];

        if (isset($mobileAccess) && $mobileAccess !== $result->mobile_access) {
            $data['last_modified_mobile_access_user_id'] = auth()->user()->id;
        }

        $result->update($data);

        if (auth()->user()->hasAnyRole(['admin', 'super-admin'])) {
            $result->roles()->detach();
            $result->urcRoles()->detach();

            $profiles->map(function ($profile, $urcId) use ($result) {
                $result->assignRole($profile->pluck('role_id')->toArray(), $urcId);
            });
        }

        if ($result->id === auth()->user()->id) {
            $result->load([
                'currentRole',
                'city.federalUnit',
                'urcRoles',
                'currentUrgencyRegulationCenter:urgency_regulation_centers.id,urgency_regulation_centers.name',
                'urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
                'urgencyRegulationCenters.userRoles' => function ($query) use ($result) {
                    $query->where('user_id', $result->id);
                },
                'occupation',
            ]);
        }

        return response()->json(new UserResource($result));
    }

    /**
     * PUT api/users/password/reset
     *
     * Realiza a atualização da senha de um usuário
     *
     * @bodyParam new_password_confirmation string Confirmação da nova senha do usuário. Example: 12345678
     */
    public function resetPassword(UpdateUserPasswordRequest $request): JsonResponse
    {
        $result = User::find($request->get('user_id')) ?: auth()->user();

        if (!(($request->get('user_id') != auth()->user()->id && auth()->user()?->hasRole(['super-admin', 'admin'])) ||
            ($request->get('user_id') == auth()->user()->id && bcrypt($request->current_password == $result['password'])))) {
            abort(Response::HTTP_FORBIDDEN, 'Você não tem permissão para realizar essa ação.');
        }

        $result?->update(['password' => bcrypt($request->get('new_password'))]);

        $result?->load([
            'currentRole',
            'city.federalUnit',
            'urcRoles' => function ($query) use ($result) {
                $query->where('urc_id', $result?->urc_id);
            },
            'currentUrgencyRegulationCenter:urgency_regulation_centers.id,urgency_regulation_centers.name',
            'urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
            'urgencyRegulationCenters.userRoles' => function ($query) use ($result) {
                $query->where('user_id', $result?->id);
            },
            'lastPasswordHistory',
            'occupation',
        ]);

        return response()->json(new UserResource($result));
    }

    /*
     * GET api/cadsus-consultation/{identifier}
     *
     * Realiza a busca de um usuário, na base do CADSUS.
     *
     * @urlParam identifier, string required CPF ou CNS do usuário.
     */
    public function cadsusConsultation($identifier): JsonResponse
    {
        $result = (new CadsusService())->consult($identifier);

        return response()->json($result);
    }

    /**
     * GET api/users/able-professionals
     *
     * Retorna uma lista paginada de usuários (id e nome), filtrado por perfil.
     *
     * A listagem deve receber os seguintes filtros:
     * - Perfil
     * - Informar se deve filtrar pela CRU do usuário logado
     */
    public function ableProfessionals(DutyReportAbleProfessionalsRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = User::select('id', 'name')
            ->with('urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name')
            ->whereHas('urcRoles', function ($query) use ($data) {
                $query->whereIn('roles.name', $data['roles']);
            })
            ->when(!empty($data['filter_urc']), function ($query) {
                $query->whereHas('urgencyRegulationCenters', function ($query) {
                    $query->where('urgency_regulation_centers.id', auth()->user()->urc_id);
                });
            })
            ->when(!empty($data['search']), function ($query) use ($data) {
                $query->whereRaw('unaccent(users.name) ilike unaccent(?)', "%{$data['search']}%");
            })
            ->paginate(10);

        return UserResource::collection($results);
    }
}
