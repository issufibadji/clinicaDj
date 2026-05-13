<?php

namespace App\Actions\Clinica\Doctors;

use App\Models\Doctor;

class UpdateDoctorAction
{
    public function handle(Doctor $doctor, string $specialty, string $crm, ?string $departmentId, bool $isAvailable): void
    {
        $doctor->update([
            'specialty'     => $specialty,
            'crm'           => $crm,
            'department_id' => $departmentId,
            'is_available'  => $isAvailable,
        ]);
    }
}
