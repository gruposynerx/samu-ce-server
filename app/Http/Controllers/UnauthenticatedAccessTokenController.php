<?php

namespace App\Http\Controllers;

use App\Models\UnauthenticatedAccessToken;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Ramsey\Uuid\Uuid;

#[Group('Token não autenticado', 'Rotas para gestão de token não autenticado')]
class UnauthenticatedAccessTokenController extends Controller
{
    /**
     * POST /api/unauthenticated-access-tokens
     *
     * Cria um novo token não autenticado
     */
    public function store(): JsonResponse
    {
        $result = UnauthenticatedAccessToken::create([
            'created_by' => auth()->id(),
            'token' => Uuid::uuid4(),
            'expires_at' => now()->addMonth(),
        ]);

        return response()->json(['access_token' => $result->token]);
    }
}
