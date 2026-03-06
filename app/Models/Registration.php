<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'ticket_quantity',
        'total_amount',
        'payment_status',
        'payment_intent_id',
        'qr_code',
        'checked_in',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
