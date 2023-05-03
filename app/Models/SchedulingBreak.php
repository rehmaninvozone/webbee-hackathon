<?php

namespace App\Models;

use App\Traits\TimeBetween;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulingBreak extends Model
{
    use HasFactory, TimeBetween;

    protected $fillable = [
        'scheduling_id',
        'name',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function scheduling(): BelongsTo
    {
        return $this->belongsTo(Scheduling::class);
    }
}
