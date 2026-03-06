<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventCategory>
 */
class EventCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word());

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => fake()->sentence(),
            'icon'        => null,
        ];
    }
}
