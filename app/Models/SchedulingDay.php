<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulingDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduling_id',
        'date',
        'opening_time',
        'closing_time',
    ];

    protected $casts = [
        'opening_time' => TimeCast::class,
        'closing_time' => TimeCast::class,
        'date' => 'date',
    ];

    public function scheduling(): BelongsTo
    {
        return $this->belongsTo(Scheduling::class);
    }
}
