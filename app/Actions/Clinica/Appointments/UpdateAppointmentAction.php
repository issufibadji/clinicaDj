<?php

namespace App\Actions\Clinica\Appointments;

use App\Models\Appointment;

class UpdateAppointmentAction
{
    public function handle(Appointment $appointment, string $patientId, string $doctorId, ?string $roomId, string $scheduledAt, string $status, ?string $notes): void
    {
        $appointment->update([
            'patient_id'   => $patientId,
            'doctor_id'    => $doctorId,
            'room_id'      => $roomId,
            'scheduled_at' => $scheduledAt,
            'status'       => $status,
            'notes'        => $notes,
        ]);
    }
}
