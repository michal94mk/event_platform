<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    public const PROVIDER_GOOGLE_CALENDAR = 'google_calendar';

    public const PROVIDER_STRIPE = 'stripe';

    public const PROVIDER_SENDGRID = 'sendgrid';

    public const PROVIDER_TWILIO = 'twilio';

    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
        'provider_user_id',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'settings' => 'array',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isGoogleCalendar(): bool
    {
        return $this->provider === self::PROVIDER_GOOGLE_CALENDAR;
    }
}
