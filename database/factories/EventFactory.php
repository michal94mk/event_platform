<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'user_id'          => User::factory()->organizer(),
            'title'            => $title,
            'description'      => fake()->paragraphs(2, true),
            'slug'             => Str::slug($title),
            'start_date'       => now()->addDays(7),
            'end_date'         => now()->addDays(7)->addHours(3),
            'venue_name'       => fake()->company(),
            'venue_address'    => fake()->streetAddress(),
            'venue_city'       => fake()->city(),
            'venue_country'    => 'Poland',
            'max_attendees'    => null,
            'ticket_price'     => null,
            'currency'         => 'PLN',
            'status'           => 'published',
            'cover_image'      => null,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }

    public function past(): static
    {
        return $this->state([
            'start_date' => now()->subDays(7),
            'end_date'   => now()->subDays(7)->addHours(3),
        ]);
    }

    public function paid(float $price = 50.0): static
    {
        return $this->state(['ticket_price' => $price]);
    }

    public function withCapacity(int $max): static
    {
        return $this->state(['max_attendees' => $max]);
    }
}
