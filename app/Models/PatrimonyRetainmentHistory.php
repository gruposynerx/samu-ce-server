<?php

namespace App\Models;

use App\Observers\UrcObserver;
use App\Scopes\UrcScope;
use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperPatrimonyRetainmentHistory
 */
class PatrimonyRetainmentHistory extends Model
{
    use HasFactory, HasUrcId, HasUuids;

    protected $fillable = [
        'patrimony_id',
        'responsible_professional',
        'retained_at',
        'retained_by',
        'released_at',
        'released_by',
        'attendance_id',
        'radio_operation_id',
        'urc_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::observe(new UrcObserver());
        static::addGlobalScope(new UrcScope());
    }

    public function patrimony(): BelongsTo
    {
        return $this->belongsTo(Patrimony::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function retainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'retained_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function radioOperation(): BelongsTo
    {
        return $this->belongsTo(RadioOperation::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(RadioOperationNote::class, 'patrimony_id', 'patrimony_id');
    }
}
