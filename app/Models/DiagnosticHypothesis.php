<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin IdeHelperDiagnosticHypothesis
 */
class DiagnosticHypothesis extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'is_active',
    ];

    public function natureTypes(): BelongsToMany
    {
        return $this->belongsToMany(NatureType::class, 'nature_diagnostic_hypotheses');
    }

    public function medicalRegulations(): MorphToMany
    {
        return $this->morphedByMany(MedicalRegulation::class, 'form', 'form_diagnostic_hypotheses');
    }

    public function sceneRecordings(): MorphToMany
    {
        return $this->morphedByMany(SceneRecording::class, 'form', 'form_diagnostic_hypotheses');
    }
}
