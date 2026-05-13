<?php

namespace App\Actions\Clinica\Expenses;

use App\Models\Expense;

class UpdateExpenseAction
{
    public function handle(Expense $expense, string $description, float $amount, string $category, string $date): void
    {
        $expense->update([
            'description' => $description,
            'amount'      => $amount,
            'category'    => $category,
            'date'        => $date,
        ]);
    }
}
