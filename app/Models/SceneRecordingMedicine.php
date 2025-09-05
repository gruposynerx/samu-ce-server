<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSceneRecordingMedicine
 */
class SceneRecordingMedicine extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'scene_recording_id',
        'medicine_id',
        'quantity',
        'observations',
    ];

    public function sceneRecording(): BelongsTo
    {
        return $this->belongsTo(SceneRecording::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
