<?php

namespace App\Models;

use App\Models\Sigtap\Icd;
use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin IdeHelperSceneRecording
 */
class SceneRecording extends Model
{
    use HasUrcId, HasUuids;

    protected $fillable = [
        'urc_id',
        'attendance_id',
        'created_by',
        'scene_description',
        'icd_code',
        'victim_type',
        'security_equipment',
        'bleeding_type_id',
        'sweating_type_id',
        'skin_coloration_type_id',
        'priority_type_id',
        'observations',
        'antecedent_type_id',
        'allergy',
        'support_needed',
        'support_needed_description',
        'is_accident_at_work',
        'conduct_types',
        'closed',
        'closing_type_id',
        'death_at',
        'death_type',
        'death_professional',
        'death_professional_registration_number',
        'applied',
        'recommended',
        'closed_justification',
        'destination_unit_contact',
        'vacancy_type_id',
    ];

    protected $casts = [
        'conduct_types' => 'array',
        'support_needed_description' => 'array',
        'death_at' => 'datetime:Y-m-d\TH:i',
    ];

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function metrics(): MorphMany
    {
        return $this->morphMany(Metric::class, 'metricable');
    }

    public function wounds(): HasMany
    {
        return $this->hasMany(SceneRecordingWound::class, 'scene_recording_id');
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(SceneRecordingProcedure::class, 'scene_recording_id');
    }

    public function destinationUnitHistories(): HasMany
    {
        return $this->hasMany(SceneRecordingDestinationUnitHistory::class, 'scene_recording_id');
    }

    public function latestDestinationUnitHistory(): HasOne
    {
        return $this->hasOne(SceneRecordingDestinationUnitHistory::class, 'scene_recording_id')->latest();
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(SceneRecordingMedicine::class, 'scene_recording_id');
    }

    public function conducts(): HasMany
    {
        return $this->hasMany(SceneRecordingConduct::class, 'scene_recording_id');
    }

    public function natureType(): BelongsTo
    {
        return $this->belongsTo(NatureType::class);
    }

    public function icd(): BelongsTo
    {
        return $this->belongsTo(Icd::class, 'icd_code', 'code');
    }

    public function bleedingType(): BelongsTo
    {
        return $this->belongsTo(BleedingType::class);
    }

    public function sweatingType(): BelongsTo
    {
        return $this->belongsTo(SweatingType::class);
    }

    public function skinColorationType(): BelongsTo
    {
        return $this->belongsTo(SkinColorationType::class);
    }

    public function priorityType(): BelongsTo
    {
        return $this->belongsTo(PriorityType::class);
    }

    public function antecedentType(): BelongsTo
    {
        return $this->belongsTo(AntecedentType::class);
    }

    public function closingType(): BelongsTo
    {
        return $this->belongsTo(ClosingType::class);
    }

    public function antecedentsTypes(): HasMany
    {
        return $this->hasMany(SceneRecordingAntecedent::class, 'scene_recording_id');
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

    public function vacancyType(): BelongsTo
    {
        return $this->belongsTo(TransferReason::class, 'vacancy_type_id');
    }
    public function unitDestination(): BelongsTo
{
    return $this->belongsTo(Unit::class, 'unit_destination_id');
}
}
