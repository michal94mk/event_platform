<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'slug',
        'start_date',
        'end_date',
        'venue_name',
        'venue_address',
        'venue_city',
        'venue_country',
        'venue_latitude',
        'venue_longitude',
        'max_attendees',
        'ticket_price',
        'currency',
        'status',
        'cover_image',
        'google_calendar_event_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'ticket_price' => 'decimal:2',
            'venue_latitude' => 'decimal:8',
            'venue_longitude' => 'decimal:8',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(EventCategory::class, 'event_category_event');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isFree(): bool
    {
        return $this->ticket_price === null || (float) $this->ticket_price === 0.0;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
