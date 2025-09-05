<?php

namespace App\Providers;

use App\Classes\Zapi;
use App\Contracts\WhatsAppMessageApi;
use App\Models\Base;
use App\Models\MedicalRegulation;
use App\Models\OtherAttendance;
use App\Models\PersonalAccessToken;
use App\Models\PlaceManagement;
use App\Models\PrimaryAttendance;
use App\Models\RadioOperation;
use App\Models\RadioOperationFleet;
use App\Models\Role;
use App\Models\SceneRecording;
use App\Models\SecondaryAttendance;
use App\Models\UrgencyRegulationCenter;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        JsonResource::wrap('results');

        $this->app->bind(WhatsAppMessageApi::class, function (Application $app) {
            return $app->make(Zapi::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Relation::enforceMorphMap([
            'user' => User::class,
            'role' => Role::class,
            'primary_attendance' => PrimaryAttendance::class,
            'secondary_attendance' => SecondaryAttendance::class,
            'other_attendance' => OtherAttendance::class,
            'medical_regulation' => MedicalRegulation::class,
            'scene_recording' => SceneRecording::class,
            'radio_operation' => RadioOperation::class,
            'radio_operation_fleet' => RadioOperationFleet::class,
            'vehicle' => Vehicle::class,
            'place_management' => PlaceManagement::class,
            'base' => Base::class,
            'urgency_regulation_center' => UrgencyRegulationCenter::class,
        ]);
    }
}
