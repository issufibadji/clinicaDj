<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => fake()->name(),
            'cpf'          => fake()->unique()->numerify('###.###.###-##'),
            'birth_date'   => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'phone'        => fake()->numerify('(##) #####-####'),
            'email'        => fake()->optional()->safeEmail(),
            'address'      => null,
            'insurance_id' => null,
        ];
    }
}
