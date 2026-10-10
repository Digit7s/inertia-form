<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'title', 'notes', 'category', 'tags', 'priority', 'start_date',
        'start_time', 'appointment_at', 'attachment', 'details',
    ];

    protected function casts(): array
    {
        return ['tags' => 'array', 'details' => 'array'];
    }
}
