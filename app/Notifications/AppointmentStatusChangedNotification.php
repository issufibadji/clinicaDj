<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentStatusChangedNotification extends Notification
{
    private static array $STATUS_LABELS = [
        'scheduled'  => 'Agendado',
        'confirmed'  => 'Confirmado',
        'completed'  => 'Concluído',
        'cancelled'  => 'Cancelado',
    ];

    private static array $STATUS_COLORS = [
        'scheduled'  => 'blue',
        'confirmed'  => 'green',
        'completed'  => 'slate',
        'cancelled'  => 'red',
    ];

    public function __construct(
        private readonly Appointment $appointment,
        private readonly string $oldStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $appointment = $this->appointment->loadMissing(['patient', 'doctor.user']);
        $newStatus   = $appointment->status;
        $label       = self::$STATUS_LABELS[$newStatus] ?? $newStatus;
        $color       = self::$STATUS_COLORS[$newStatus] ?? 'slate';

        return [
            'type'           => 'appointment_status_changed',
            'icon'           => 'calendar-days',
            'color'          => $color,
            'title'          => __('Status de Agendamento Alterado'),
            'body'           => __(':patient → :status', [
                'patient' => $appointment->patient->name,
                'status'  => $label,
            ]),
            'url'            => '/clinica/agendamentos',
            'appointment_id' => $appointment->id,
            'old_status'     => $this->oldStatus,
            'new_status'     => $newStatus,
        ];
    }
}
