<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Notification;

class NewPaymentNotification extends Notification
{
    public function __construct(private readonly Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $payment = $this->payment->loadMissing(['appointment.patient']);

        return [
            'type'       => 'new_payment',
            'icon'       => 'banknotes',
            'color'      => 'green',
            'title'      => __('Novo Pagamento Recebido'),
            'body'       => __(':patient — R$ :amount', [
                'patient' => $payment->appointment->patient->name,
                'amount'  => number_format($payment->amount, 2, ',', '.'),
            ]),
            'url'        => '/clinica/pagamentos',
            'payment_id' => $payment->id,
        ];
    }
}
