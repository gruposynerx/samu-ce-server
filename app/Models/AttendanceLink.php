<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAttendanceLink
 */
class AttendanceLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'father_link_id',
        'created_by',
        'children_link_id',
    ];

    public function fatherLink(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'father_link_id');
    }

    public function childrenLink(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'children_link_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
