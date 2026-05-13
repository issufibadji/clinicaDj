<?php

namespace App\Actions\Clinica\Payments;

use App\Models\Payment;

class UpdatePaymentAction
{
    public function handle(Payment $payment, float $amount, string $method, string $status): void
    {
        $payment->update([
            'amount' => $amount,
            'method' => $method,
            'status' => $status,
            'paid_at' => $status === 'paid' && ! $payment->paid_at ? now() : $payment->paid_at,
        ]);
    }
}
