<?php

namespace App\Http\Controllers;

use App\Enums\PlaceStatusEnum;
use App\Http\Requests\ChangeUrgencyRegulationCenterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Authentication', 'Endpoints for authentication')]
#[Subgroup('User Urgency Regulation Centers', 'Endpoints for manage user urgency regulation centers')]
class UserUrgencyRegulationCenterController extends Controller
{
    /**
     * PUT /api/change-urgency-regulation-center
     *
     * Altera a CRU do usuário logado
     */
    public function changeUrgencyRegulationCenter(ChangeUrgencyRegulationCenterRequest $request): JsonResponse
    {
        $result = auth()->user();

        if ($result?->place) {
            $result->place->update([
                'user_id' => null,
                'place_status_id' => PlaceStatusEnum::FREE->value,
            ]);
        }

        $result->update($request->validated());

        $query = $result->urcRoles()->where('urc_id', $result->urc_id);

        if (!$query->get()->contains($result->currentRole->id)) {
            $profile = $query->first()->id;

            $result->update([
                ...$request->validated(),
                'current_role' => $profile,
            ]);
        }

        return response()->json(
            Auth::user()->load([
                'currentRole',
                'city.federalUnit',
                'urcRoles',
                'currentUrgencyRegulationCenter:urgency_regulation_centers.id,urgency_regulation_centers.name',
                'urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
                'urgencyRegulationCenters.userRoles',
                'place',
            ])
        );
    }
}
