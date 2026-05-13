<?php

namespace App\Actions\Clinica\Patients;

use App\Models\Patient;

class UpdatePatientAction
{
    public function handle(Patient $patient, string $name, string $cpf, string $birthDate, string $phone, ?string $email, ?string $insuranceId): void
    {
        $patient->update([
            'name'         => $name,
            'cpf'          => $cpf,
            'birth_date'   => $birthDate,
            'phone'        => $phone,
            'email'        => $email,
            'insurance_id' => $insuranceId,
        ]);
    }
}
