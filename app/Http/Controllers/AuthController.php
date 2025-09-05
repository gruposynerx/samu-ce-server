<?php

namespace App\Http\Controllers;

use App\Enums\UserLogLoginTypeEnum;
use App\Enums\UserStatusEnum;
use App\Exceptions\AuthException;
use App\Http\Requests\AuthUserRequest;
use App\Http\Requests\CheckAuthRequest;
use App\Http\Resources\UrgencyRegulationCenterResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\UrgencyRegulationCenter;
use App\Models\User;
use App\Models\UserLog;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Knuckles\Scribe\Attributes\Group;

#[Group('Authentication', 'Endpoints for authentication')]
class AuthController extends Controller
{
    public function __construct(public AttendanceService $attendanceService)
    {
    }

    private function checkUserInactive(User $user): void
    {
        if ($user->latestStatusesHistory->status_id === UserStatusEnum::INACTIVE->value) {
            throw AuthException::userInactive();
        }
    }

    /**
     * GET /api/auth/check-credentials
     *
     * Check if the user credentials are valid. If they are, return the user id and the bases that the user has access to.
     *
     * @response 200 {
     *  "user_id": "9aaee001-08da-4cf1-8a27-fa70241315d1",
     *  "urgency_regulation_centers": [
     *      {
     *          "id": "9aaee2ee-8d07-4478-b0ed-a4e405ca1753",
     *          "city_id": 3,
     *          "name": "Central de Regulação de Urgência 1",
     *          "street": "Cale Prairie",
     *          "house_number": "4815",
     *          "neighborhood": "Hoyt Mews",
     *          "reference_place": "Ledner Springs",
     *          "is_active": false
     *      }
     *    ]
     * }
     *
     * @unauthenticated
     */
    public function checkCredentials(CheckAuthRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!Auth::validate($request->only(['identifier', 'password']))) {
            throw AuthException::invalidCredentials();
        }

        $user = User::where('identifier', $data['identifier'])->first();
        $userId = $user->id;
        $userName = $user->name;

        $this->checkUserInactive($user);

        $agent = new Agent();

        $avaliableUserBases = UrgencyRegulationCenter::whereHas('userRoles', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        $isMobile = $agent->isMobile() || ($data['mobile_detected'] ?? false);

        if (!$user->mobile_access && $isMobile) {
            $userAgent = $agent->isDesktop() ? $agent->platform() : $agent->device();

            $currentDatetime = now();

            $userLogData = $avaliableUserBases->map(fn ($urc) => [
                'user_log_login_type_id' => UserLogLoginTypeEnum::BLOCKED_BY_MOBILE_ACCESS_NOT_ALLOWED,
                'urc_id' => $urc->id,
                'name' => $userName,
                'user_agent' => $userAgent,
                'logged_at' => $currentDatetime,
                'created_at' => $currentDatetime,
                'updated_at' => $currentDatetime,
            ])->toArray();

            UserLog::withoutGlobalScopes()->insert($userLogData);

            throw AuthException::mobileAccessNotAllowed();
        }

        return response()->json([
            'user_id' => $userId,
            'user_name' => $userName,
            'urgencyRegulationCenters' => UrgencyRegulationCenterResource::collection($avaliableUserBases),
        ]);
    }

    /**
     * POST /api/auth/login
     *
     * Login the user and return the token.
     *
     * @unauthenticated
     */
    public function store(AuthUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('identifier', $data['identifier'])->first();

        if (!$user || !Auth::attempt(['identifier' => $user->identifier, 'password' => $data['password']])) {
            throw AuthException::invalidCredentials();
        }

        $this->checkUserInactive($user);

        $agent = new Agent();

        $userAgent = $agent->isDesktop() ? $agent->platform() : $agent->device();
        $userLogCommonData = [
            'name' => $user->name,
            'urc_id' => $data['urc_id'],
            'role_id' => $data['role_id'],
            'logged_at' => now(),
            'user_agent' => $userAgent,
            'longitude' => $data['longitude'] ?? null,
            'latitude' => $data['latitude'] ?? null,
        ];

        $isMobile = $agent->isMobile() || ($data['mobile_detected'] ?? false);

        if (!$user->mobile_access && $isMobile) {
            UserLog::create([
                'user_log_login_type_id' => UserLogLoginTypeEnum::BLOCKED_BY_MOBILE_ACCESS_NOT_ALLOWED,
                ...$userLogCommonData,
            ]);

            throw AuthException::mobileAccessNotAllowed();
        }

        $selectedRole = Role::find($data['role_id']);

        $userDoesntHaveRole = $user->urcRoles
            ->where('pivot.urc_id', $request->get('urc_id'))
            ->where('pivot.role_id', $request->get('role_id'))->isEmpty();

        if ($userDoesntHaveRole) {
            throw AuthException::doesntHaveSelectedRole();
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token', [$selectedRole->name], now()->addMinutes(config('sanctum.expiration')))->plainTextToken;

        $user->update([
            'urc_id' => $data['urc_id'],
            'current_role' => $data['role_id'],
            'last_seen' => now(),
        ]);

        if ($request->has('push_token')) {
            $pushTokenData = [
                'token' => $request->get('push_token'),
                'platform' => $request->get('platform', $agent->platform()),
                'device_id' => $request->get('device_id'),
            ];

            try {
                $existingToken = $user->pushTokens()
                    ->where('device_id', $pushTokenData['device_id'])
                    ->first();

                if ($existingToken) {
                    $existingToken->update([
                        'token' => $pushTokenData['token'],
                        'platform' => $pushTokenData['platform'],
                    ]);
                } else {
                    $pushTokenData['id'] = (string) Str::orderedUuid();
                    $user->pushTokens()->create($pushTokenData);
                }
            } catch (\Exception $e) {
                throw AuthException::pushTokenError($e->getMessage());
            }
        }

        UserLog::create([
            'user_log_login_type_id' => UserLogLoginTypeEnum::COMPLETED_SUCCESSFULLY,
            ...$userLogCommonData,
        ]);

        return response()->json([
            'access_token' => $token,
        ]);
    }

    /**
     * POST /api/auth/logout
     *
     * Logout the user.
     */
    public function logout(): Response
    {
        $user = Auth::user();

        $this->attendanceService->abandonOccupiedPlace();

        $this->attendanceService->abandonAttendancesInProgress();

        $user?->tokens()->delete();

        $user->update([
            'urc_id' => null,
            'current_role' => null,
        ]);

        return response()->noContent();
    }

    public function me(): JsonResponse
    {
        $authUser = Auth::user();

        $result = $authUser?->load([
            'currentRole',
            'city.federalUnit',
            'urcRoles' => function ($query) use ($authUser) {
                $query->where('urc_id', $authUser?->urc_id);
            },
            'currentUrgencyRegulationCenter:urgency_regulation_centers.id,urgency_regulation_centers.name',
            'urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
            'urgencyRegulationCenters.userRoles' => function ($query) use ($authUser) {
                $query->where('user_id', $authUser?->id);
            },
            'lastPasswordHistory',
            'place',
            'occupation',
        ]);

        $result->hasPowerBI = $result->powerBIReports->isNotEmpty();

        return response()->json(new UserResource($result));
    }
}
