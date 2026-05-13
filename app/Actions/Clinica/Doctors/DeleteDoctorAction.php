<?php

namespace App\Actions\Clinica\Doctors;

use App\Models\Doctor;
use Illuminate\Validation\ValidationException;

class DeleteDoctorAction
{
    public function handle(Doctor $doctor): void
    {
        if ($doctor->appointments()->exists()) {
            throw ValidationException::withMessages([
                'doctor' => 'Não é possível excluir: existem agendamentos vinculados a este médico.',
            ]);
        }

        $doctor->delete();
    }
}
