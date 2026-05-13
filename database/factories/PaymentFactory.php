<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'amount'         => fake()->randomFloat(2, 50, 500),
            'method'         => fake()->randomElement(['cash', 'card', 'pix', 'insurance']),
            'status'         => 'pending',
            'paid_at'        => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(['status' => 'paid', 'paid_at' => now()]);
    }
}
