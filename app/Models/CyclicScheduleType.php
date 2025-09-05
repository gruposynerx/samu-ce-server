<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCyclicScheduleType
 */
class CyclicScheduleType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'work_hours',
        'break_hours',
        'is_active',
    ];
}
