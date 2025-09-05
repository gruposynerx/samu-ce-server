<?php

namespace App\Models;

use App\Observers\UrcObserver;
use App\Scopes\UrcScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperTicket
 */
class Ticket extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'requester_id',
        'urc_id',
        'created_by',
        'ticket_type_id',
        'city_id',
        'multiple_victims',
        'number_of_victims',
        'ticket_sequence_per_urgency_regulation_center',
        'opening_at',
        'message_id',
        'receive_notification',
    ];

    protected $casts = [
        'opening_at' => 'datetime:Y-m-d\TH:i',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::observe(new UrcObserver());
        static::addGlobalScope(new UrcScope());

        static::creating(function ($ticket) {
            $last = $ticket->where('urc_id', auth()->user()->urc_id)->max('ticket_sequence_per_urgency_regulation_center');

            $ticket->ticket_sequence_per_urgency_regulation_center = $last + 1;
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Requester::class);
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function geolocation(): HasOne
    {
        return $this->hasOne(TicketGeolocation::class);
    }
}
