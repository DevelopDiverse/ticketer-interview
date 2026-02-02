<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'starts_at' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'capacity' => random_int(50, 1000),
        ];
    }
}
