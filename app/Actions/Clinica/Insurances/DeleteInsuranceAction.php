<?php

namespace App\Actions\Clinica\Insurances;

use App\Models\Insurance;
use Illuminate\Validation\ValidationException;

class DeleteInsuranceAction
{
    public function handle(Insurance $insurance): void
    {
        if ($insurance->patients()->exists()) {
            throw ValidationException::withMessages([
                'insurance' => 'Não é possível excluir: existem pacientes vinculados a este convênio.',
            ]);
        }

        $insurance->delete();
    }
}
