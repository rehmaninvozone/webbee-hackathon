<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduling_id',
        'name',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function scheduling(): BelongsTo
    {
        return $this->belongsTo(Scheduling::class);
    }
}
