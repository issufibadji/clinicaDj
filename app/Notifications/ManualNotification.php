<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ManualNotification extends Notification
{
    public function __construct(
        private readonly string $batchId,
        private readonly string $title,
        private readonly string $body,
        private readonly string $icon,
        private readonly string $color,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'manual',
            'batch_id' => $this->batchId,
            'icon'     => $this->icon,
            'color'    => $this->color,
            'title'    => $this->title,
            'body'     => $this->body,
            'url'      => $this->url,
        ];
    }
}
