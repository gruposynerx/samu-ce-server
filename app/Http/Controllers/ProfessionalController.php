<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Profissionais', description: 'Seção responsável pela gestão de profissionais.')]
class ProfessionalController extends Controller
{
    /**
     * GET api/professionals
     *
     * Retorna uma lista paginada de usuários com CBO´s específicos.
     */
    public function index(IndexUserRequest $request): ResourceCollection
    {
        $search = $request->validated('search');
        $cbo = $request->validated('cbo');
        $roleIds = $request->validated('role_ids');

        $results = (new UserService())->getUsers($search ?? '', true, $cbo, $roleIds);

        return UserResource::collection($results);
    }

    /**
     * POST api/professionals
     *
     * Realiza o cadastro de um profissional.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = (new UserService())->createUser($data);

        return response()->json($result);
    }
}
