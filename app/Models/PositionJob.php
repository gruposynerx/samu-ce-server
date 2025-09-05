<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionJob extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'regional_group_id'
    ];


    public function userSchedules()
    {
        return $this->hasMany(UserSchedule::class, 'position_jobs_id');
    }
    

}
