<?php

namespace App\Actions\Clinica\Patients;

use App\Models\Patient;
use Illuminate\Validation\ValidationException;

class DeletePatientAction
{
    public function handle(Patient $patient): void
    {
        if ($patient->appointments()->exists()) {
            throw ValidationException::withMessages([
                'patient' => 'Não é possível excluir: existem agendamentos vinculados a este paciente.',
            ]);
        }

        $patient->delete();
    }
}
