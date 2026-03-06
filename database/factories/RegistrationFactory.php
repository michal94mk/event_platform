<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id'        => Event::factory(),
            'user_id'         => User::factory(),
            'first_name'      => fake()->firstName(),
            'last_name'       => fake()->lastName(),
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => null,
            'ticket_quantity' => 1,
            'total_amount'    => '0.00',
            'payment_status'  => 'paid',
            'qr_code'         => Str::random(32),
            'checked_in'      => false,
            'checked_in_at'   => null,
        ];
    }

    public function checkedIn(): static
    {
        return $this->state([
            'checked_in'    => true,
            'checked_in_at' => now(),
        ]);
    }

    public function guest(): static
    {
        return $this->state(['user_id' => null]);
    }
}
