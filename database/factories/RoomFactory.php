<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => 'Sala ' . fake()->bothify('##?'),
            'type'          => fake()->randomElement(['Consultório', 'Exames', 'Cirurgia', 'Triagem']),
            'capacity'      => fake()->numberBetween(1, 10),
            'department_id' => null,
            'is_active'     => true,
        ];
    }

    public function forDepartment(Department $department): static
    {
        return $this->state(['department_id' => $department->id]);
    }
}
