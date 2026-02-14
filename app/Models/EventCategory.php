<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_category_event');
    }
}
