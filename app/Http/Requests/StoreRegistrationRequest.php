<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event && $event->status === 'published';
    }

    public function rules(): array
    {
        $event = $this->route('event');

        $maxAttendees = $event->max_attendees ?? 99999;
        $currentCount = $event->registrations()->count();
        $maxTickets = max(1, $maxAttendees - $currentCount);

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'ticket_quantity' => ['required', 'integer', 'min:1', 'max:'.$maxTickets],
        ];
    }
}
