<?php

namespace App\Models;

use App\Enums\AttendanceStatusEnum;
use App\Mail\EmailResetPassword;
use App\Observers\UserPasswordHistoryObserver;
use App\Observers\UserStatusHistoryObserver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable {
        assignRole as assignRoleToModel;
        hasAnyRole as hasAnyRoleFromModel;
    }

    protected $fillable = [
        'city_id',
        'name',
        'identifier',
        'national_health_card',
        'email',
        'password',
        'birthdate',
        'gender_code',
        'phone',
        'whatsapp',
        'neighborhood',
        'street_type',
        'street',
        'house_number',
        'complement',
        'council_number',
        'cbo',
        'is_active',
        'urc_id',
        'current_role',
        'last_seen',
        'mobile_access',
        'last_modified_mobile_access_user_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::observe([UserPasswordHistoryObserver::class, UserStatusHistoryObserver::class]);
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = url(config('app.url') . route('password.reset', [
            'token' => $token,
            'email' => $this->email,
        ], false));

        Mail::to($this->email)->send(new EmailResetPassword($this->name, $url));
    }

    /**
     * Assign the given role to the model.
     *
     * @param  string|array|Role|Collection|\BackedEnum  $roles
     * @param  UrgencyRegulationCenter|string|Collection<int,UrgencyRegulationCenter>  $urgencyRegulationCenter
     * @return HasRoles
     */
    public function assignRole($roles, $urgencyRegulationCenter = null)
    {
        $roles = self::collectRoles($roles);

        if ($urgencyRegulationCenter || $this->urc_id) {
            $urgencyRegulationCenter = $urgencyRegulationCenter ?? $this->urc_id;

            if (is_string($urgencyRegulationCenter)) {
                $urgencyRegulationCenter = UrgencyRegulationCenter::where('id', $urgencyRegulationCenter)->get();
            } elseif ($urgencyRegulationCenter instanceof UrgencyRegulationCenter) {
                $urgencyRegulationCenter = collect([$urgencyRegulationCenter]);
            }

            $urgencyRegulationCenter->each(function (UrgencyRegulationCenter $urc) use ($roles) {
                foreach ($roles as $roleId) {
                    UserRole::firstOrCreate([
                        'user_id' => $this->id,
                        'role_id' => $roleId,
                        'urc_id' => $urc->id,
                    ]);
                }
            });
        }

        return self::assignRoleToModel($roles);
    }

    public function hasAnyRole(array $roles): bool
    {
        $user = auth()->user();

        $canRoleResult = self::hasAnyRoleFromModel($roles);

        return $canRoleResult && ($user->current_role && in_array($user->currentRole->name, $roles));
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function urgencyRegulationCenters(): BelongsToMany
    {
        return $this->belongsToMany(UrgencyRegulationCenter::class, 'user_roles', 'user_id', 'urc_id')
            ->withPivot('role_id')
            ->withTimestamps()
            ->select('urgency_regulation_centers.*', 'user_roles.user_id')
            ->distinct('urgency_regulation_centers.id', 'user_roles.user_id');
    }

    public function currentUrgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id', 'id');
    }

    public function userAttendances(): HasMany
    {
        return $this->hasMany(UserAttendance::class, 'user_id', 'id');
    }

    public function attendancesInProgress(): HasManyThrough
    {
        return $this->hasManyThrough(Attendance::class, AttendanceLog::class, 'user_id', 'id', 'id', 'attendance_id')
            ->whereIn('attendances.attendance_status_id', AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE)
            ->whereHas('latestLog', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->distinct();
    }

    public function currentRole(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'current_role');
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(UserPasswordHistory::class, 'user_id', 'id');
    }

    public function lastPasswordHistory(): HasOne
    {
        return $this->hasOne(UserPasswordHistory::class, 'user_id', 'id')->latest();
    }

    public function urcRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->using(UserRole::class)
            ->withPivot('urc_id')
            ->withTimestamps();
    }

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class, 'cbo', 'code');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class, 'created_by', 'id');
    }

    public function latestStatusesHistory(): HasOne
    {
        return $this->hasOne(UserStatusHistory::class, 'user_id', 'id')->latest();
    }

    public function statusesHistory(): HasMany
    {
        return $this->hasMany(UserStatusHistory::class, 'user_id', 'id');
    }

    public function place(): HasOne
    {
        return $this->hasOne(PlaceManagement::class, 'user_id', 'id');
    }

    public function powerBIReports(): BelongsToMany
    {
        return $this->belongsToMany(PowerBIReport::class, 'power_bi_report_roles', 'role_id', 'power_bi_report_id', 'current_role', 'id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(UserSchedule::class);
    }

    public function schedulesSchemas(): HasMany
    {
        return $this->hasMany(UserScheduleSchema::class);
    }

    public function pushTokens(): HasMany
    {
        return $this->hasMany(PushToken::class);
    }
}
