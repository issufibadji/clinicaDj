<?php

namespace App\Actions\Clinica\Expenses;

use App\Models\Expense;
use App\Models\User;

class CreateExpenseAction
{
    public function handle(User $user, string $description, float $amount, string $category, string $date): Expense
    {
        return Expense::create([
            'user_id'     => $user->id,
            'description' => $description,
            'amount'      => $amount,
            'category'    => $category,
            'date'        => $date,
        ]);
    }
}
