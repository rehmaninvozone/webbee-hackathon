<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduling_id',
        'appointment_id',
        'start_time',
        'end_time',
        'date',
        'is_available',
    ];

    protected $casts = [
        'start_time' => TimeCast::class,
        'end_time' => TimeCast::class,
        'date' => 'date'
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function scheduling(): HasOne
    {
        return $this->hasOne(Scheduling::class);
    }
}
