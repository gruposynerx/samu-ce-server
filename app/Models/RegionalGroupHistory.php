<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRegionalGroupHistory
 */
class RegionalGroupHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'regional_group_history',
        'previous_regional_group_name',
        'current_regional_group_name',
        'current_status',
        'previous_linked_bases',
        'current_linked_bases',
    ];
}
