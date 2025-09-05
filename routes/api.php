<?php

use App\Http\Controllers\AttendanceCancellationRecordController;
use App\Http\Controllers\AttendanceConsultationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceDataController;
use App\Http\Controllers\AttendanceEvolutionController;
use App\Http\Controllers\AttendanceLinkController;
use App\Http\Controllers\AttendanceMonitoringController;
use App\Http\Controllers\AttendanceObservationController;
use App\Http\Controllers\AttendanceRecordHistoryController;
use App\Http\Controllers\AttendanceSceneRecordingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\BaseVehiclesController;
use App\Http\Controllers\BPAController;
use App\Http\Controllers\CallsConsultationController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CityVehiclesController;
use App\Http\Controllers\CoordinationNoteController;
use App\Http\Controllers\CyclicScheduleTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagnosticHypothesisController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\DuplicateAttendanceController;
use App\Http\Controllers\DutyReportController;
use App\Http\Controllers\FederalUnitController;
use App\Http\Controllers\FormSettingController;
use App\Http\Controllers\GeocodingController;
use App\Http\Controllers\IcdController;
use App\Http\Controllers\MedicalRegulationController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MobileDeviceController;
use App\Http\Controllers\MonitoringSettingController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\OccurrenceController;
use App\Http\Controllers\OtherAttendanceController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PatrimonyController;
use App\Http\Controllers\PatrimonyRetainmentsController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\PlaceManagementController;
use App\Http\Controllers\PowerBIReportController;
use App\Http\Controllers\PrankCallController;
use App\Http\Controllers\PrimaryAttendanceController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RadioOperationController;
use App\Http\Controllers\RadioOperationFleetController;
use App\Http\Controllers\RegionalGroupController;
use App\Http\Controllers\RequesterSatisfactionController;
use App\Http\Controllers\SatisfactionConsultationController;
use App\Http\Controllers\SceneRecordingController;
use App\Http\Controllers\SceneRecordingCounterreferralController;
use App\Http\Controllers\SecondaryAttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SigtapController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UnauthenticatedAccessTokenController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UrgencyRegulationCenterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserScheduleClocksController;
use App\Http\Controllers\UserScheduleController;
use App\Http\Controllers\UserSchedulesSchemaController;
use App\Http\Controllers\UserStatusHistoryController;
use App\Http\Controllers\UserUrgencyRegulationCenterController;
use App\Http\Controllers\VehicleConsultationController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehiclePatrimonyController;
use App\Http\Controllers\VehicleStatusHistoryController;
use App\Http\Controllers\VehicleTrackingController;
use App\Http\Controllers\WorkplaceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleConsultationController;
use App\Http\Controllers\CallsConsultationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('forgot-password', [PasswordResetController::class, 'sendMail'])->middleware('guest')->name('password.email');

Route::middleware(['device.validate'])->prefix('auth')->group(function () {
    Route::get('/check-credentials', [AuthController::class, 'checkCredentials'])->name('auth.check-credentials');
    Route::post('/login', [AuthController::class, 'store'])->name('auth.store');
});

Route::prefix('user')->group(function () {
    Route::get('/roles-by-unit', [UserRoleController::class, 'fetchRolesByUnitAndUser'])->name('user.fetch-roles-by-unit-and-user');
});

Route::prefix('attendance-monitoring')->group(function () {
    Route::get('/{ticketId}', [AttendanceMonitoringController::class, 'show'])->name('attendance-monitoring.show');
    Route::post('/requester-satisfaction', [RequesterSatisfactionController::class, 'store'])->name('attendance-monitoring.requester-satisfaction.store');
});

Route::middleware(['auth:sanctum', 'last_seen', 'device.validate'])->group(function () {
    Route::prefix('monitoring-settings')->group(function () {
        Route::get('/', [MonitoringSettingController::class, 'show'])->name('attendance-monitoring.settings.show');
        Route::put('/', [MonitoringSettingController::class, 'update'])->name('attendance-monitoring.settings.update');
    });

    Route::get('me', [AuthController::class, 'me'])->name('auth.me');
    Route::put('change-profile', [UserRoleController::class, 'changeProfile'])->name('user.change-profile');
    Route::put('change-urgency-regulation-center', [UserUrgencyRegulationCenterController::class, 'changeUrgencyRegulationCenter'])->name('user.change-urgency-regulation-center');

    Route::get('icds', [IcdController::class, 'index'])->name('icds.index');
    Route::get('procedures', [ProcedureController::class, 'index'])->name('procedures.index');
    Route::get('medicines', [MedicineController::class, 'index'])->name('medicines.index');

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    Route::get('urgency-regulation-centers', [UrgencyRegulationCenterController::class, 'index'])->name('urgency-regulation-centers.index');
    Route::get('federal-units', [FederalUnitController::class, 'index'])->name('federal-units.index');
    Route::get('cities', [CityController::class, 'index'])->name('cities.index');

    Route::prefix('prank-call')->group(function () {
        Route::get('check', [PrankCallController::class, 'check'])->name('prank-call.check');
    });

    Route::prefix('diagnostic-hypothesis')->group(function () {
        Route::get('/', [DiagnosticHypothesisController::class, 'index'])->name('diagnostic-hypothesis.index');

        Route::middleware(['role:super-admin|admin'])->group(function () {
            Route::post('/', [DiagnosticHypothesisController::class, 'store'])->name('diagnostic-hypothesis.store');
            Route::put('/{id}', [DiagnosticHypothesisController::class, 'update'])->name('diagnostic-hypothesis.update');
            Route::put('change-status/{id}', [DiagnosticHypothesisController::class, 'changeStatus'])->name('diagnostic-hypothesis.change-status');
        });
    });

    Route::middleware(['role:admin|super-admin|coordinator'])->group(function () {
        Route::post('sigtap/import', [SigtapController::class, 'store'])->name('sigtap.store');

        Route::get('cadsus-consultation/{identifier}', [UserController::class, 'cadsusConsultation'])->name('cadsus-consultation');
    });

    Route::prefix('users')->group(function () {
        Route::middleware(['role:admin|super-admin'])->group(function () {
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::post('/{id}/status', [UserStatusHistoryController::class, 'store'])->name('users.status.store');
        });

        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/able-professionals', [UserController::class, 'ableProfessionals'])->name('users.able-professionals');
        Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::put('/password/reset', [UserController::class, 'resetPassword'])->name('users.password.reset');
    });

    Route::prefix('ticket')->group(function () {
        Route::prefix('/primary-attendance')->group(function () {
            Route::middleware(['role:admin|super-admin|TARM|medic|attendance-or-ambulance-team|team-leader'])->group(function () {
                Route::get('/', [PrimaryAttendanceController::class, 'index'])->name('ticket.primary-attendance.index');
            });

            Route::middleware(['role:admin|super-admin|TARM|medic|radio-operator|team-leader'])->group(function () {
                Route::get('/{id}', [PrimaryAttendanceController::class, 'show'])->name('ticket.primary-attendance.show');
                Route::post('/', [PrimaryAttendanceController::class, 'store'])->name('ticket.primary-attendance.store');
                Route::put('/{id}', [PrimaryAttendanceController::class, 'update'])->name('ticket.primary-attendance.update');
            });
        });

        Route::prefix('/secondary-attendance')->group(function () {
            Route::middleware(['role:admin|super-admin|TARM|medic|attendance-or-ambulance-team|team-leader'])->group(function () {
                Route::get('/', [SecondaryAttendanceController::class, 'index'])->name('ticket.secondary-attendance.index');
            });

            Route::middleware(['role:admin|super-admin|TARM|medic|radio-operator|team-leader'])->group(function () {
                Route::get('/{id}', [SecondaryAttendanceController::class, 'show'])->name('ticket.secondary-attendance.show');
                Route::post('/', [SecondaryAttendanceController::class, 'store'])->name('ticket.secondary-attendance.store');
                Route::put('/{id}', [SecondaryAttendanceController::class, 'update'])->name('ticket.secondary-attendance.update');
            });
        });

        Route::middleware(['role:admin|super-admin|TARM|medic|radio-operator|team-leader'])->group(function () {
            Route::prefix('/other-attendance')->group(function () {
                Route::post('/', [OtherAttendanceController::class, 'store'])->name('ticket.other-attendance.store');
            });
        });

        Route::post('/{ticketId}/save-geolocation', [TicketController::class, 'saveGeolocation'])->name('ticket.save-geolocation');
    });

    Route::prefix('attendance')->group(function () {
        Route::middleware(['role:admin|super-admin|medic|TARM|radio-operator|attendance-or-ambulance-team|reports-consulting|team-leader|coordinator'])->group(function () {
            Route::get('/consultation', [AttendanceConsultationController::class, 'index'])->name('attendance.consultation.index');
        });

        Route::middleware(['role:admin|super-admin|reports-consulting'])->group(function () {
            Route::get('/indicator/iam', [IAMConsultationController::class, 'index'])->name('attendance.consultation-iam.index');
        });

        Route::put('/{id}/close', [AttendanceController::class, 'close'])->name('attendance.close');
        Route::get('/{id}/links', [AttendanceController::class, 'links'])->name('attendance.links');

        Route::middleware(['role:admin|super-admin|TARM|medic|radio-operator|attendance-or-ambulance-team|team-leader'])->group(function () {
            Route::get('/observation/{attendanceId}', [AttendanceObservationController::class, 'index'])->name('attendance.observation.index');
            Route::post('/observation', [AttendanceObservationController::class, 'store'])->name('attendance.observation.store');
            Route::post('/duplicate', DuplicateAttendanceController::class)->name('attendance.duplicate');
        });

        Route::middleware(['role:admin|super-admin|TARM|medic|radio-operator|attendance-or-ambulance-team|team-leader|coordinator|reports-consulting'])->group(function () {
            Route::get('/{id}', [AttendanceDataController::class, 'show'])->name('attendance.show');
        });

        Route::middleware(['role:admin|super-admin|medic|team-leader'])->group(function () {
            Route::post('/cancellation', [AttendanceCancellationRecordController::class, 'store'])->name('attendance.cancellation.store');
        });

        Route::middleware(['role:admin|super-admin|medic|attendance-or-ambulance-team'])->group(function () {
            Route::post('/evolution', [AttendanceEvolutionController::class, 'store'])->name('attendance.evolution.store');
        });

        Route::prefix('/check')->group(function () {
            Route::get('/medical-regulation/{id}', [MedicalRegulationController::class, 'check'])->name('attendance.check.medical-regulation');
            Route::get('/scene-recording/{id}', [SceneRecordingController::class, 'check'])->name('attendance.check.scene-recording');
            Route::get('/radio-operation/{id}', [RadioOperationController::class, 'check'])->name('attendance.check.radio-operation');
            Route::get('/equals', [AttendanceController::class, 'equals'])->name('attendance.check.equals');
        });

        Route::prefix('/link')->group(function () {
            Route::get('/{attendanceId}', [AttendanceLinkController::class, 'index'])->name('attendance.link.index');
            Route::post('/', [AttendanceLinkController::class, 'store'])->name('attendance.link.store');
        });
    });

    Route::prefix('medical-regulation')->group(function () {
        Route::middleware(['role:admin|super-admin|medic|radio-operator|team-leader'])->group(function () {
            Route::post('start-attendance/{id}', [MedicalRegulationController::class, 'start'])->name('medical-regulation.start');
            Route::get('/latest/{attendanceId}', [MedicalRegulationController::class, 'latest'])->name('medical-regulation.latest');
            Route::post('/', [MedicalRegulationController::class, 'store'])->name('medical-regulation.store');
        });

        Route::get('/{attendanceId}', [MedicalRegulationController::class, 'index'])
            ->name('medical-regulation.index')
            ->middleware(['role:admin|super-admin|medic|radio-operator|team-leader|TARM|coordinator']);
    });

    Route::prefix('vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('vehicles.index');

        Route::middleware(['role:admin|super-admin|fleet-control|radio-operator'])->group(function () {
            Route::get('/{id}', [VehicleController::class, 'show'])->name('vehicles.show');
            Route::post('/', [VehicleController::class, 'store'])->name('vehicles.store');
            Route::put('/{id}', [VehicleController::class, 'update'])->name('vehicles.update');
            Route::post('/force', [VehicleController::class, 'forceStore'])->name('vehicles.force-store');
            Route::put('/force/{id}', [VehicleController::class, 'forceUpdate'])->name('vehicles.force-update');

            Route::prefix('/{id}/status')->group(function () {
                Route::post('/', [VehicleStatusHistoryController::class, 'store'])->name('vehicles.status.store');
            });

            Route::prefix('/{id}/patrimonies')->group(function () {
                Route::get('/', [VehiclePatrimonyController::class, 'index'])->name('vehicles.patrimonies.index');
                Route::patch('/', [VehiclePatrimonyController::class, 'update'])->name('vehicles.patrimonies.update');
                Route::delete('/{patrimonyId}', [VehiclePatrimonyController::class, 'destroy'])->name('vehicles.patrimonies.destroy');
            });
        });
    });

    Route::middleware(['role:admin|super-admin|fleet-control|radio-operator'])->group(function () {
        Route::prefix('patrimonies')->group(function () {
            Route::get('/', [PatrimonyController::class, 'index'])->name('patrimonies.index');
            Route::get('/{id}', [PatrimonyController::class, 'show'])->name('patrimonies.show');
            Route::post('/', [PatrimonyController::class, 'store'])->name('patrimonies.store');
            Route::put('/{id}', [PatrimonyController::class, 'update'])->name('patrimonies.update');
        });

        Route::prefix('/patrimony-retainments')->group(function () {
            Route::get('/', [PatrimonyRetainmentsController::class, 'index'])->name('patrimony-retainement.store');
            Route::post('/{id}/release', [PatrimonyRetainmentsController::class, 'release'])->name('patrimony-retainments.release');
        });
    });

    Route::prefix('bases')->group(function () {
        Route::get('/', [BaseController::class, 'index'])->name('bases.index');
        Route::get('/urc/{id}', [BaseController::class, 'listByUrc'])->name('bases.listByUrc');
        Route::get('/tracking-bases', [BaseController::class, 'trackingBases'])->name('bases.tracking-bases');

        Route::middleware(['role:admin|super-admin'])->group(function () {
            Route::post('/', [BaseController::class, 'store'])->name('bases.store');
            Route::put('/{id}', [BaseController::class, 'update'])->name('bases.update');
            Route::get('/{id}', [BaseController::class, 'show'])->name('bases.show');
            Route::put('/change-status/{id}', [BaseController::class, 'changeStatus'])->name('base.change-status');
        });
    });

    Route::group([
        'middleware' => 'role:super-admin|admin|radio-operator|medic|team-leader|TARM',
    ], static function () {
        Route::get('base-vehicles', [BaseVehiclesController::class, 'index'])->name('base-vehicles.index');
        Route::get('city-vehicles', [CityVehiclesController::class, 'index'])->name('city-vehicles.index');
    });

    Route::prefix('units')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('units.index');
        Route::get('/fetch-by-registration/{registration}', [UnitController::class, 'fetchByRegistration'])->name('units.fetch-by-registration');

        Route::middleware(['role:admin|super-admin'])->group(function () {
            Route::get('/{id}', [UnitController::class, 'show'])->name('units.show');
            Route::post('/', [UnitController::class, 'store'])->name('units.store');
            Route::put('/{id}', [UnitController::class, 'update'])->name('units.update');
            Route::put('/change-status/{id}', [UnitController::class, 'changeStatus'])->name('units.change-status');
        });
    });

    Route::get('/attendance/indicator/iam/', [IAMConsultationController::class, 'index'])->name('attendance.consultation-iam.index');
    Route::get('/attendance/indicator/vehicles', [VehicleConsultationController::class, 'indexServicebyVTR'])->name('attendance.vehicle.indexServicebyVTR');
    Route::get('/attendance/indicator/vehicles/average', [VehicleConsultationController::class, 'indexAverageTimeByType'])->name('attendance.vehicle.indexAverageTimeByType');
    Route::get('/attendance/indicator/vehicles/average-vtr', [VehicleConsultationController::class, 'averageResponseTimePerVehicle'])->name('attendance.vehicle.indexGroupedByCruAndVehicleTypeVtr');
    Route::get('/attendance/indicator/nature', [AttendanceConsultationController::class, 'attendanceConsultationNature'])->name('attendance.consultation-nature.attendanceConsultation');
    Route::get('/attendance/indicator/hd', [AttendanceConsultationController::class, 'attendanceConsultationHd'])->name('attendance.consultation.attendanceConsultationHd');
    Route::get('/attendance/indicator/calls/listing', [CallsConsultationController::class, 'indexListingCallByType'])->name('attendance.consultation.indexListingCallByType');
    Route::get('/attendance/indicator/calls/dashboard', [CallsConsultationController::class, 'indexDashboardCallAll'])->name('attendance.consultation.indexDashboardCallAll');

    Route::prefix('radio-operation')->group(function () {
        Route::middleware(['role:admin|super-admin|radio-operator|attendance-or-ambulance-team'])->group(function () {
            Route::prefix('/fleet')->group(function () {
                Route::get('/able-occupations/{vehicleId}', [RadioOperationFleetController::class, 'getAbleOccupations'])->name('radio-operation-fleet.get-able-occupations');
                Route::get('/able-professionals', [RadioOperationFleetController::class, 'getAbleProfessionals'])->name('radio-operation-fleet.get-able-professionals');
            });
            Route::post('confirm-fleet/{fleetId}', [RadioOperationController::class, 'confirmFleet'])->name('radio-operation.confirm-fleet');
            Route::post('start-attendance/{id}', [RadioOperationController::class, 'start'])->name('radio-operation.start');
            Route::get('/', [RadioOperationController::class, 'index'])->name('radio-operation.index');
            Route::post('/', [RadioOperationController::class, 'store'])->name('radio-operation.store');
            Route::put('/{id}/update-fleet', [RadioOperationController::class, 'updateFleet'])->name('radio-operation.update-fleet');
            Route::put('/{id}', [RadioOperationController::class, 'update'])->name('radio-operation.update');
        });

        Route::get('/by-vehicle/{vehicleId}', [RadioOperationController::class, 'getAttendancesByVehicle'])->name('radio-operation.get-attendances-by-vehicle');

        Route::get('/show-by-attendance/{attendanceId}', [RadioOperationController::class, 'showByAttendance'])
            ->middleware(['role:admin|super-admin|radio-operator|TARM|medic|team-leader|coordinator|attendance-or-ambulance-team'])
            ->name('radio-operation.show-by-attendance');
    });

    Route::prefix('coordination-notes')->group(function () {
        Route::get('/', [CoordinationNoteController::class, 'index'])->name('coordination-notes.index');
        Route::get('/{id}', [CoordinationNoteController::class, 'show'])->name('coordination-notes.show');

        Route::middleware(['role:admin|super-admin'])->group(function () {
            Route::post('/', [CoordinationNoteController::class, 'store'])->name('coordination-notes.store');
            Route::put('/{id}', [CoordinationNoteController::class, 'update'])->name('coordination-notes.update');
            Route::delete('/{id}', [CoordinationNoteController::class, 'destroy'])->name('coordination-notes.destroy');
        });
    });

    Route::prefix('scene-recording')->group(function () {
        Route::middleware(['role:super-admin|admin|medic|attendance-or-ambulance-team|team-leader'])->group(function () {
            Route::post('start-attendance/{id}', [SceneRecordingController::class, 'start'])->name('scene-recording.start');
            Route::post('/', [SceneRecordingController::class, 'store'])->name('scene-recording.store');
            Route::post('/counter-referral', [SceneRecordingCounterreferralController::class, 'store'])->name('scene-recording.counter-referral.store');
        });

        Route::middleware(['role:super-admin|admin|medic|attendance-or-ambulance-team|team-leader|radio-operator|TARM|coordinator'])->group(function () {
            Route::get('/identifiers/{attendance_id}', [AttendanceSceneRecordingController::class, 'identifiers'])->name('scene-recording.identifiers');
            Route::get('/{id}', [SceneRecordingController::class, 'show'])->name('scene-recording.show');
        });
    });

    Route::prefix('profiles')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profiles.index');
    });

    Route::prefix('occupations')->group(function () {
        Route::get('/', [OccupationController::class, 'index'])->name('occupations.index');
        Route::get('/{id}', [OccupationController::class,'show'])->name('occupations.show');
    });

    Route::prefix('report')->group(function () {
        Route::prefix('/duty')->group(function () {
            Route::middleware('role:super-admin|admin|team-leader|radio-operator')->group(function () {
                Route::get('/', [DutyReportController::class, 'index'])->name('report.duty.index');
                Route::post('/', [DutyReportController::class, 'store'])->name('report.duty.store');
                Route::get('/verify', [DutyReportController::class, 'verifyExistenceOfPreviousReport'])->name('report.duty.verify-existence-of-previous-report');
            });

            Route::get('/{id}', [DutyReportController::class, 'show'])->name('report.duty.show')->middleware('role:super-admin|admin');
            Route::put('/{id}', [DutyReportController::class, 'update'])->name('report.duty.update')->middleware('role:team-leader|radio-operator|super-admin');
        });

        Route::middleware('role:super-admin|admin|team-leader|reports-consulting|manager')->group(function () {
            Route::prefix('/bpa')->group(function () {
                Route::post('/', [BPAController::class, 'generate'])->name('bpa.generate');
            });
        });
    });

    Route::post('unauthenticated-access-tokens', [UnauthenticatedAccessTokenController::class, 'store'])->name('unauthenticated-access-tokens.store')->middleware('role:super-admin|manager');

    Route::group([
        'prefix' => 'dashboard',
    ], static function () {
        Route::middleware('role:super-admin|admin|manager')->group(function () {
            Route::get('removals', [DashboardController::class, 'fetchRemovals'])->name('dashboard.fetch-removals');
            Route::get('attendance-base-statuses', [DashboardController::class, 'attendanceBaseStatuses'])->name('dashboard.attendance-base-statuses');
            Route::get('committed-vehicles', [DashboardController::class, 'fetchCommittedVehicles'])->name('dashboard.committed-vehicles');
            Route::get('occurrences', [DashboardController::class, 'fetchOccurrences'])->name('dashboard.attendance-count');
            Route::get('fleet', [DashboardController::class, 'fleet'])->name('dashboard.fleet');
            Route::get('retained-patrimonies', [DashboardController::class, 'retainedPatrimonies'])->name('dashboard.available-vehicles');
            Route::get('attendances-per-professional', [DashboardController::class, 'fetchAttendancesPerProfessional'])->name('dashboard.attendances-per-professional');
        });

        Route::get('occurrence-types-by-attendance-status', [DashboardController::class, 'fetchOccurrenceTypesByAttendanceStatus'])
            ->name('dashboard.occurrence-types-by-attendance-status')
            ->middleware('role:super-admin|admin|medic|TARM|attendance-or-ambulance-team|team-leader|manager');
    });

    Route::group([
        'prefix' => 'vehicles-tracking',
    ], function () {
        Route::get('/all-vehicles-current-location', [VehicleTrackingController::class, 'allVehiclesCurrentLocation'])->name('vehicles-tracking.all-vehicles-current-location');
        Route::get('/fetch-vehicle-history', [VehicleTrackingController::class, 'fetchVehicleHistory'])->name('vehicles-tracking.fetch-vehicle-history');
        Route::get('/nearby-vehicles', [VehicleTrackingController::class, 'getNearbyVehicles'])->name('vehicles-tracking.nearby-vehicles');
    });

    Route::group(['prefix' => 'geocoding'], function () {
        Route::get('/geocode', [GeocodingController::class, 'geocode'])->name('geocoding.geocode');
        Route::get('/reverse-geocode', [GeocodingController::class, 'reverseGeocode'])->name('geocoding.reverse-geocode');
    })->withoutMiddleware(['auth:sanctum', 'last_seen', 'device.validate', 'api']);

    Route::group(['prefix' => 'draft'], function () {
        Route::get('/', [DraftController::class, 'index'])->name('draft.index');
        Route::get('/{id}', [DraftController::class, 'show'])->name('draft.show');
        Route::post('/', [DraftController::class, 'store'])->name('draft.store');
        Route::delete('/{id}', [DraftController::class, 'destroy'])->name('draft.destroy');
    });

    Route::group(['prefix' => 'regional-group'], function () {
        Route::get('/', [RegionalGroupController::class, 'index'])->name('regional-group.index');
        Route::get('/{id}', [RegionalGroupController::class, 'show'])->name('regional-group.show');
        Route::post('/', [RegionalGroupController::class, 'store'])->name('regional-group.store');
        Route::put('/{id}', [RegionalGroupController::class, 'update'])->name('regional-group.update');
        Route::put('/change-status/{id}', [RegionalGroupController::class, 'changeStatus'])->name('regional-group.change-status');
    });

    Route::middleware(['role:admin|super-admin|radio-operator|team-leader|medic|TARM'])->group(function () {
        Route::prefix('place')->group(function () {
            Route::get('/', [PlaceManagementController::class, 'index'])->name('place.index');
            Route::put('/{id}', [PlaceManagementController::class, 'update'])->name('place.update');
            Route::put('/vacate/{id}', [PlaceManagementController::class, 'vacate'])->name('place.vacate');
            Route::put('/occupy/{id}', [PlaceManagementController::class, 'occupy'])->name('place.occupy');
            Route::put('/activate-or-inactivate/{id}', [PlaceManagementController::class, 'activateOrInactivate'])->name('place.activate-or-inactivate');
            Route::post('/', [PlaceManagementController::class, 'store'])->name('place.store')->middleware('role:admin|super-admin');
        });
    });

    Route::get('/attendance-record-history/{attendanceId}', [AttendanceRecordHistoryController::class, 'show'])->name('records.history');

    Route::middleware(['role:super-admin|admin|medic|attendance-or-ambulance-team|manager'])->group(function () {
        Route::prefix('mobile-device')->group(function () {
            Route::get('/', [MobileDeviceController::class, 'index'])->name('mobile-device.index');
            Route::post('/', [MobileDeviceController::class, 'store'])->name('mobile-device.store');
            Route::get('/able-vehicles', [MobileDeviceController::class, 'getAbleVehicles'])->name('mobile-device.vehicles.index');
            Route::put('/{mobileDevice}', [MobileDeviceController::class, 'update'])->name('mobile-device.update');
            Route::patch('/{mobileDevice}/unlink', [MobileDeviceController::class, 'unlinkDevice'])->name('mobile-device.unlink');
        });

        Route::get('/user-logs', [UserLogController::class, 'index'])->name('user-logs.index');
        Route::prefix('cyclic-schedule-type')->group(function () {
            Route::get('/', [CyclicScheduleTypeController::class, 'index'])->name('cyclic-schedule-type.index');
            Route::post('/', [CyclicScheduleTypeController::class, 'store'])->name('cyclic-schedule-type.store');
            Route::put('/{id}', [CyclicScheduleTypeController::class, 'update'])->name('cyclic-schedule-type.update');
            Route::put('/change-status/{id}', [CyclicScheduleTypeController::class, 'changeStatus'])->name('cyclic-schedule-type.change-status');
        });
    });

    Route::prefix('power-bi-report')->group(function () {
        Route::get('/', [PowerBIReportController::class, 'index'])->name('power-bi-report.index');

        Route::middleware(['role:super-admin'])->group(function () {
            Route::post('/', [PowerBIReportController::class, 'store'])->name('power-bi-report.store');
            Route::put('/{id}', [PowerBIReportController::class, 'update'])->name('power-bi-report.update');
            Route::delete('/{id}', [PowerBIReportController::class, 'destroy'])->name('power-bi-report.destroy');
        });
    });

    Route::prefix('schedules-schema')->group(function () {
        Route::middleware('role:super-admin|admin')->group(function () {
            Route::get('/', [UserSchedulesSchemaController::class, 'index'])->name('schedules-schema.index');
        });
        Route::middleware('role:super-admin')->group(function () {
            Route::get('/{id}', [UserSchedulesSchemaController::class, 'show'])->name('schedules-schema.show');
            Route::post('/', [UserSchedulesSchemaController::class, 'store'])->name('schedules-schema.store');
            Route::put('/{id}', [UserSchedulesSchemaController::class, 'update'])->name('schedules-schema.update');
        });
    });

    Route::prefix('schedules')->group(function () {
        Route::middleware('role:super-admin|schedule-manager|admin')->group(function () {
            Route::post('/', [UserScheduleController::class, 'store'])->name('schedules.store');
            Route::get('/', [UserScheduleController::class, 'index'])->name('schedules.index');
            Route::patch('/{id}', [UserScheduleController::class, 'update'])->name('schedules.update');
            Route::get('/user-schedules', [UserScheduleController::class, 'getUsers'])->name('schedules.getUsers');
            Route::delete('/{id}', [UserScheduleController::class, 'destroy'])->name('schedules.destroy');
            Route::get('/reports', [UserScheduleController::class, 'reportSchedule'])->name('schedules.reportSchedule');
            Route::get('/balance-hours', [UserScheduleController::class, 'balanceHoursSchedule'])->name('schedules.balanceHoursSchedule');
            Route::post('/register-point', [UserScheduleClocksController::class, 'registerPoint'])->name('schedules.registerPoint');
        });
    });

    Route::prefix('schedules-events')->group(function () {
        Route::middleware('role:super-admin|schedule-manager|admin')->group(function () {
            Route::post('/', [ScheduleEventController::class, 'store'])->name('schedules-events.store');
            Route::get('/types', [ScheduleEventController::class, 'listTypes'])->name('schedules-events.listTypes');
            Route::get('/professional-reverse/{id}', [ScheduleEventController::class, 'findEventsByReverseUserId'])->name('schedules-events.findEventsByReverseUserId');
            Route::patch('/{id}', [ScheduleEventController::class, 'update'])->name('schedules-events.update');
        });
    });

    Route::get('workplace', [WorkplaceController::class, 'index']);

    Route::prefix('professionals')->group(function () {
        Route::get('/', [ProfessionalController::class, 'index'])->name('professionals.index');
        Route::middleware(['role:super-admin|admin|schedule-manager'])->group(function () {
            Route::post('/', [ProfessionalController::class, 'store'])->name('professionals.store');
        });
    });

    Route::prefix('my-last-occurrences')->group(function () {
        Route::get('/', [OccurrenceController::class, 'myLastAttendances'])->name('my-last-attendances.index');
        Route::get('/{id}', [OccurrenceController::class, 'myLastAttendancesShow'])->name('my-last-attendances.show');
    });

    Route::prefix('shifts')->group(function () {
        Route::middleware(['role:super-admin|admin|schedule-manager'])->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->name('shifts.index');
            Route::post('/', [ShiftController::class, 'store'])->name('shifts.store');
            Route::patch('/{id}', [ShiftController::class, 'update'])->name('shifts.update');
            Route::delete('/{id}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
        });
    });

    Route::prefix('form-setting')->group(function () {
        Route::get('/', [FormSettingController::class, 'show'])->name('form.setting.show');
        Route::put('/', [FormSettingController::class, 'update'])->name('form.setting.update');
    });

    Route::post('notifications/mark-as-viewed', [RadioOperationController::class, 'markNotificationAsRead'])->name('notifications.mark-as-viewed');
});

Route::group(['prefix' => 'unauthenticated', 'middleware' => 'unauthenticated'], static function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('occurrences-per-diagnostic-hypothesis', [DashboardController::class, 'occurrencesPerDiagnosticHypothesis'])->name('dashboard.occurrences-per-diagnostic-hypothesis');
        Route::get('attendance-base-statuses', [DashboardController::class, 'attendanceBaseStatuses'])->name('dashboard.attendance-base-statuses');
        Route::get('average-response-time', [DashboardController::class, 'averageResponseTime'])->name('dashboard.average-response-time');
        Route::get('occurrences', [DashboardController::class, 'fetchOccurrences'])->name('dashboard.attendance-count');
        Route::get('prank-calls', [DashboardController::class, 'prankCalls'])->name('dashboard.prank-calls');
        Route::get('fleet', [DashboardController::class, 'fleet'])->name('dashboard.fleet');
    });
});

Route::prefix('pins')->group(function () {
    Route::get('/check', [PinController::class, 'checkPin'])->name('pin.check');
    Route::patch('/sync-device', [PinController::class, 'syncDevice'])->name('pin.sync-device');
});

Route::get('vehicles/able-occupations/{vehicleType}', [RadioOperationFleetController::class, 'getAbleOccupationsByType'])->name('radio-operation-fleet.get-able-occupations');
