<?php

namespace App\Actions\Clinica\Appointments;

use App\Models\Appointment;
use Illuminate\Validation\ValidationException;

class DeleteAppointmentAction
{
    public function handle(Appointment $appointment): void
    {
        if ($appointment->payment()->exists()) {
            throw ValidationException::withMessages([
                'appointment' => 'Não é possível excluir: existe pagamento vinculado a este agendamento.',
            ]);
        }

        $appointment->delete();
    }
}
