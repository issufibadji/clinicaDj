<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InsuranceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => fake()->company() . ' Saúde',
            'plan_type'     => fake()->randomElement(['Básico', 'Intermediário', 'Premium']),
            'contact_phone' => fake()->optional()->numerify('(##) #####-####'),
            'is_active'     => true,
        ];
    }
}
