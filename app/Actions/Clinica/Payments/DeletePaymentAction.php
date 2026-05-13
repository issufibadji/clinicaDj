<?php

namespace App\Actions\Clinica\Payments;

use App\Models\Payment;
use Illuminate\Validation\ValidationException;

class DeletePaymentAction
{
    public function handle(Payment $payment): void
    {
        if ($payment->status === 'paid') {
            throw ValidationException::withMessages([
                'payment' => 'Não é possível excluir um pagamento com status "pago".',
            ]);
        }

        $payment->delete();
    }
}
