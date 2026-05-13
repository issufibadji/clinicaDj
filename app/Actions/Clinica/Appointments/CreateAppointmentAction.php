<?php

namespace App\Actions\Clinica\Appointments;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentCreatedNotification;
use Illuminate\Support\Facades\Notification;

class CreateAppointmentAction
{
    public function handle(string $patientId, string $doctorId, ?string $roomId, string $scheduledAt, ?string $notes): Appointment
    {
        $appointment = Appointment::create([
            'patient_id'   => $patientId,
            'doctor_id'    => $doctorId,
            'room_id'      => $roomId,
            'scheduled_at' => $scheduledAt,
            'status'       => 'scheduled',
            'notes'        => $notes,
        ]);

        $this->notify($appointment);

        return $appointment;
    }

    private function notify(Appointment $appointment): void
    {
        $appointment->load('doctor.user');

        $recipients = User::role(['admin', 'super-admin'])->get();

        $doctorUser = $appointment->doctor->user ?? null;
        if ($doctorUser && ! $recipients->contains($doctorUser)) {
            $recipients->push($doctorUser);
        }

        Notification::send($recipients, new AppointmentCreatedNotification($appointment));
    }
}
