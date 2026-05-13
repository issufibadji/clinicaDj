<?php

namespace App\Actions\Clinica\Appointments;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class UpdateAppointmentAction
{
    public function handle(Appointment $appointment, string $patientId, string $doctorId, ?string $roomId, string $scheduledAt, string $status, ?string $notes): void
    {
        $oldStatus = $appointment->status;

        $appointment->update([
            'patient_id'   => $patientId,
            'doctor_id'    => $doctorId,
            'room_id'      => $roomId,
            'scheduled_at' => $scheduledAt,
            'status'       => $status,
            'notes'        => $notes,
        ]);

        if ($oldStatus !== $status) {
            $this->notifyStatusChange($appointment, $oldStatus);
        }
    }

    private function notifyStatusChange(Appointment $appointment, string $oldStatus): void
    {
        $appointment->load('doctor.user');

        $recipients = User::role(['admin', 'super-admin'])->get();

        $doctorUser = $appointment->doctor->user ?? null;
        if ($doctorUser && ! $recipients->contains($doctorUser)) {
            $recipients->push($doctorUser);
        }

        Notification::send($recipients, new AppointmentStatusChangedNotification($appointment, $oldStatus));
    }
}
