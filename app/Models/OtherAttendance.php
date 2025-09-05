<?php

namespace App\Models;

use App\Scopes\AttendanceScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin IdeHelperOtherAttendance
 */
class OtherAttendance extends Model
{
    use HasUuids;

    protected $fillable = [
        'description',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new AttendanceScope());
    }

    public function attendable(): MorphOne
    {
        return $this->morphOne(Attendance::class, 'attendable');
    }
}
