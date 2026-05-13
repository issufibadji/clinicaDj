<?php

namespace App\Actions\Clinica\Events;

use App\Models\Event;
use App\Models\User;

class CreateEventAction
{
    public function handle(User $user, string $title, ?string $description, string $startAt, string $endAt, string $color, bool $isPublic): Event
    {
        return Event::create([
            'user_id'     => $user->id,
            'title'       => $title,
            'description' => $description,
            'start_at'    => $startAt,
            'end_at'      => $endAt,
            'color'       => $color,
            'is_public'   => $isPublic,
        ]);
    }
}
