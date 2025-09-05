<?php

namespace App\Models;

use App\Models\Sigtap\Procedure;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSceneRecordingProcedure
 */
class SceneRecordingProcedure extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'scene_recording_id',
        'procedure_code',
        'observations',
    ];

    public function sceneRecording(): BelongsTo
    {
        return $this->belongsTo(SceneRecording::class);
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class, 'procedure_code', 'code');
    }
}
