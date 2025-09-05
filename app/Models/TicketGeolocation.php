<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTicketAdress
 */
class TicketGeolocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_id',
        'ticket_id',
        'address',
        'location',
        'viewport',
        'formatted_address',
    ];

    protected $casts = [
        'address' => AsArrayObject::class,
        'location' => AsArrayObject::class,
        'viewport' => AsArrayObject::class,
    ];

    protected $table = 'ticket_geolocations';

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
