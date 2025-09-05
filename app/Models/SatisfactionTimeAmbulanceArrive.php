<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSatisfactionTimeAmbulanceArrive
 */
class SatisfactionTimeAmbulanceArrive extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'satisfaction_time_ambulance_arrive';

    protected $fillable = [
        'name',
    ];
}
