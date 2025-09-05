<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBPAReport
 */
class BPAReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    protected $table = 'bpa_reports';
}
