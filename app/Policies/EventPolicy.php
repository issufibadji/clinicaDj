<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool { return $user->can('events.view'); }
    public function view(User $user, Event $event): bool
    {
        return $user->can('events.view') || $event->is_public;
    }
    public function create(User $user): bool { return $user->can('events.create'); }
    public function update(User $user, Event $event): bool
    {
        return $user->can('events.edit') && $event->user_id === $user->id;
    }
    public function delete(User $user, Event $event): bool
    {
        return $user->can('events.delete') && $event->user_id === $user->id;
    }
}
