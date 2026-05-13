<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'description' => fake()->sentence(4),
            'amount'      => fake()->randomFloat(2, 10, 2000),
            'category'    => fake()->randomElement(['Salários', 'Insumos', 'Equipamentos', 'Manutenção', 'Aluguel', 'Outros']),
            'date'        => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
