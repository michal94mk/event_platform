<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CalendarSubscriptionToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function generateFor(User $user): self
    {
        $existing = $user->calendarSubscriptionToken;

        if ($existing && ! $existing->isExpired()) {
            return $existing;
        }

        if ($existing) {
            $existing->delete();
        }

        return self::create([
            'user_id' => $user->id,
            'token' => Str::random(48),
            'expires_at' => now()->addYear(),
        ]);
    }
}
