<?php

namespace App\Models;

use App\Events\ChangePlaces;
use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperPlaceManagement
 */
class PlaceManagement extends Model
{
    use HasFactory, HasUrcId, HasUuids, LogsActivity;

    protected $fillable = [
        'name',
        'urc_id',
        'user_id',
        'place_status_id',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::updated(function ($model) {
            if ($model->isDirty('user_id')) {
                $model->placeHistory()->create([
                    'place_id' => $model->id,
                    'user_id' => $model->user_id,
                    'urc_id' => $model->urc_id,
                ]);
            }

            $urcId = auth()->user()->urc_id;

            if ($urcId === $model->urc_id) {
                ChangePlaces::dispatch($urcId);
            }
        });
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function placeStatus(): BelongsTo
    {
        return $this->belongsTo(PlaceStatus::class, 'place_status_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable();
    }

    public function placeHistory(): HasMany
    {
        return $this->hasMany(PlaceManagementHistory::class, 'place_id');
    }
}
