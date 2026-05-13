<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'specialty'     => fake()->randomElement(['Clínica Geral', 'Cardiologia', 'Ortopedia', 'Pediatria', 'Neurologia']),
            'crm'           => fake()->unique()->numerify('CRM-#####'),
            'department_id' => null,
            'is_available'  => true,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(['is_available' => false]);
    }
}
