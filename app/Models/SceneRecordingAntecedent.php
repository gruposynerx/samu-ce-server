<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSceneRecordingAntecedent
 */
class SceneRecordingAntecedent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'scene_recording_id',
        'antecedent_type_id',
    ];

    public function sceneRecording(): BelongsTo
    {
        return $this->belongsTo(SceneRecording::class);
    }

    public function antecedentType(): BelongsTo
    {
        return $this->belongsTo(AntecedentType::class, 'antecedent_type_id');
    }
}
