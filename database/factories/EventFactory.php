<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+30 days');
        $end   = (clone $start)->modify('+1 hour');

        return [
            'user_id'     => User::factory(),
            'title'       => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'start_at'    => $start,
            'end_at'      => $end,
            'color'       => '#3B82F6',
            'is_public'   => false,
        ];
    }

    public function public(): static
    {
        return $this->state(['is_public' => true]);
    }
}
