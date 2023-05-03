<?php

namespace App\Models;

use App\Traits\TimeBetween;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory, TimeBetween;

    protected $fillable = [
        'scheduling_id',
        'service_id',
        'start_time',
        'end_time',
        'number_of_people',
        'booked_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function scheduling(): BelongsTo
    {
        return $this->belongsTo(Scheduling::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function appointmentDetails(): HasMany
    {
        return $this->hasMany(AppointmentDetail::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }
}
