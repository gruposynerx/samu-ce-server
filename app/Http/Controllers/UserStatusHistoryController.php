<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserStatusHistoryRequest;
use App\Http\Resources\UserStatusHistoryResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Usuário', description: 'Gestão de usuários')]
#[Subgroup(name: 'Status', description: 'Gestão de status do usuário')]
class UserStatusHistoryController extends Controller
{
    /**
     * POST api/users/{id}/status
     *
     * Altera o status de um usuário.
     *
     * @urlParam id string required ID do usuário.
     */
    public function store(StoreUserStatusHistoryRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = User::findOrFail($id);
        $result->tokens()->delete();

        $userStatusHistory = $result->statusesHistory()->create([...$data, 'created_by' => auth()->user()->id]);

        return response()->json(new UserStatusHistoryResource($userStatusHistory));
    }
}
