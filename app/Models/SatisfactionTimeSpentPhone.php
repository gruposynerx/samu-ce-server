<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSatisfactionTimeSpentPhone
 */
class SatisfactionTimeSpentPhone extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'satisfaction_time_spent_phone';

    protected $fillable = [
        'name',
    ];
}
