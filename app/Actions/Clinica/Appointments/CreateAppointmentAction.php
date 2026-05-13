<?php

namespace App\Actions\Clinica\Appointments;

use App\Models\Appointment;

class CreateAppointmentAction
{
    public function handle(string $patientId, string $doctorId, ?string $roomId, string $scheduledAt, ?string $notes): Appointment
    {
        return Appointment::create([
            'patient_id'   => $patientId,
            'doctor_id'    => $doctorId,
            'room_id'      => $roomId,
            'scheduled_at' => $scheduledAt,
            'status'       => 'scheduled',
            'notes'        => $notes,
        ]);
    }
}
