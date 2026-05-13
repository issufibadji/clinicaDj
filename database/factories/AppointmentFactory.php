<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id'   => Patient::factory(),
            'doctor_id'    => Doctor::factory(),
            'room_id'      => null,
            'scheduled_at' => fake()->dateTimeBetween('now', '+30 days'),
            'status'       => 'scheduled',
            'notes'        => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed', 'scheduled_at' => fake()->dateTimeBetween('-30 days', 'now')]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
