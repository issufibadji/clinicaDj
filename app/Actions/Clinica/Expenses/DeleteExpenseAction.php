<?php

namespace App\Actions\Clinica\Expenses;

use App\Models\Expense;

class DeleteExpenseAction
{
    public function handle(Expense $expense): void
    {
        $expense->delete();
    }
}
