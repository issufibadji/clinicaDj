<?php

namespace App\Actions\Clinica\Events;

use App\Models\Event;

class UpdateEventAction
{
    public function handle(Event $event, string $title, ?string $description, string $startAt, string $endAt, string $color, bool $isPublic): void
    {
        $event->update([
            'title'       => $title,
            'description' => $description,
            'start_at'    => $startAt,
            'end_at'      => $endAt,
            'color'       => $color,
            'is_public'   => $isPublic,
        ]);
    }
}
