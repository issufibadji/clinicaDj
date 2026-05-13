<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentCreatedNotification extends Notification
{
    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $appointment = $this->appointment->loadMissing(['patient', 'doctor.user']);

        return [
            'type'          => 'appointment_created',
            'icon'          => 'calendar',
            'color'         => 'blue',
            'title'         => __('Novo Agendamento'),
            'body'          => __(':patient com Dr. :doctor em :date', [
                'patient' => $appointment->patient->name,
                'doctor'  => $appointment->doctor->user->name,
                'date'    => $appointment->scheduled_at->format('d/m/Y H:i'),
            ]),
            'url'           => '/clinica/agendamentos',
            'appointment_id' => $appointment->id,
        ];
    }
}
