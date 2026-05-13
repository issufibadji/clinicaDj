<?php

namespace App\Actions\Clinica\Payments;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\NewPaymentNotification;
use Illuminate\Support\Facades\Notification;

class CreatePaymentAction
{
    public function handle(string $appointmentId, float $amount, string $method, string $status, ?string $paidAt): Payment
    {
        $payment = Payment::create([
            'appointment_id' => $appointmentId,
            'amount'         => $amount,
            'method'         => $method,
            'status'         => $status,
            'paid_at'        => $status === 'paid' ? ($paidAt ?? now()) : null,
        ]);

        if ($status === 'paid') {
            $admins = User::role(['admin', 'super-admin'])->get();
            Notification::send($admins, new NewPaymentNotification($payment));
        }

        return $payment;
    }
}
