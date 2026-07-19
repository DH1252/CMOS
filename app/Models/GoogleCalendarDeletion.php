<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleCalendarDeletion extends Model
{
    protected $fillable = [
        'timeline_id',
        'google_event_id',
        'google_calendar_id',
        'attempts',
        'last_error',
        'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'last_attempt_at' => 'datetime',
        ];
    }
}
