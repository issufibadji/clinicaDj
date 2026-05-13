<?php

namespace App\Actions\Clinica\Events;

use App\Models\Event;

class DeleteEventAction
{
    public function handle(Event $event): void
    {
        $event->delete();
    }
}
