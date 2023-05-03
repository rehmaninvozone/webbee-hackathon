<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scheduling extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'days_in_advance',
        'max_clients_per_slot',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedulingDays(): HasMany
    {
        return $this->hasMany(SchedulingDay::class);
    }

    public function schedulingBreaks(): HasMany
    {
        return $this->hasMany(SchedulingBreak::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }
}
