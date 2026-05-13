<?php

namespace App\Actions\Clinica\Doctors;

use App\Models\Doctor;

class CreateDoctorAction
{
    public function handle(string $userId, string $specialty, string $crm, ?string $departmentId): Doctor
    {
        return Doctor::create([
            'user_id'       => $userId,
            'specialty'     => $specialty,
            'crm'           => $crm,
            'department_id' => $departmentId,
            'is_available'  => true,
        ]);
    }
}
