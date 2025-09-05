<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperNatureType
 */
class NatureType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function diagnosticHypotheses(): BelongsToMany
    {
        return $this->belongsToMany(DiagnosticHypothesis::class, 'nature_diagnostic_hypotheses');
    }
}
