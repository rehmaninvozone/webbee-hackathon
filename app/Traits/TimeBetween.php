<?php

namespace App\Traits;

trait TimeBetween
{
    public function scopeWhereBetweenTime($query, $startTime, $endTime)
    {
        return $query->where(function ($query) use ($startTime, $endTime) {
            $query->where('start_time', '<=', $startTime)
                ->where('end_time', '>=', $startTime);
        })->orWhere(function ($query) use ($startTime, $endTime) {
            $query->where('start_time', '<=', $endTime)
                ->where('end_time', '>=', $endTime);
        })->orWhere(function ($query) use ($startTime, $endTime) {
            $query->where('start_time', '>=', $startTime)
                ->where('end_time', '<=', $endTime);
        });
    }
}
