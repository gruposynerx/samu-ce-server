<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin IdeHelperMedicalRegulation
 */
class MedicalRegulation extends Model
{
    use HasUrcId, HasUuids;

    protected $fillable = [
        'attendance_id',
        'created_by',
        'medical_regulation',
        'priority_type_id',
        'consciousness_level_id',
        'respiration_type_id',
        'action_type_id',
        'action_details',
        'vehicle_movement_code_id',
        'supporting_organizations',
        'urc_id',
        'destination_unit_contact',
        'created_at',
    ];

    protected $casts = [
        'action_details' => 'array',
        'supporting_organizations' => 'array',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function natureType(): BelongsTo
    {
        return $this->belongsTo(NatureType::class);
    }

    public function priorityType(): BelongsTo
    {
        return $this->belongsTo(PriorityType::class);
    }

    public function consciousnessLevel(): BelongsTo
    {
        return $this->belongsTo(ConsciousnessLevel::class);
    }

    public function respirationType(): BelongsTo
    {
        return $this->belongsTo(RespirationType::class);
    }

    public function actionType(): BelongsTo
    {
        return $this->belongsTo(ActionType::class);
    }

    public function vehicleMovementCode(): BelongsTo
    {
        return $this->belongsTo(VehicleMovementCode::class);
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function diagnosticHypotheses(): MorphToMany
    {
        return $this->morphToMany(DiagnosticHypothesis::class, 'form', 'form_diagnostic_hypotheses')
            ->withPivot(['nature_type_id', 'created_by', 'attendance_id', 'applied', 'recommended']);
    }

    public function evolutions(): MorphMany
    {
        return $this->morphMany(AttendanceEvolution::class, 'form');
    }
}
