<?php

namespace App\Actions\Clinica\Payments;

use App\Models\Payment;

class CreatePaymentAction
{
    public function handle(string $appointmentId, float $amount, string $method, string $status, ?string $paidAt): Payment
    {
        return Payment::create([
            'appointment_id' => $appointmentId,
            'amount'         => $amount,
            'method'         => $method,
            'status'         => $status,
            'paid_at'        => $status === 'paid' ? ($paidAt ?? now()) : null,
        ]);
    }
}
