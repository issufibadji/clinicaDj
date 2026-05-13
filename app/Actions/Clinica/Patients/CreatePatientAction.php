<?php

namespace App\Actions\Clinica\Patients;

use App\Models\Patient;

class CreatePatientAction
{
    public function handle(string $name, string $cpf, string $birthDate, string $phone, ?string $email, ?string $insuranceId): Patient
    {
        return Patient::create([
            'name'         => $name,
            'cpf'          => $cpf,
            'birth_date'   => $birthDate,
            'phone'        => $phone,
            'email'        => $email,
            'insurance_id' => $insuranceId,
        ]);
    }
}
