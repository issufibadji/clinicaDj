<?php

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;

class ChatMessagePolicy
{
    public function viewAny(User $user): bool { return $user->can('chat.view'); }
    public function create(User $user): bool { return $user->can('chat.send'); }
    public function delete(User $user, ChatMessage $message): bool
    {
        return $message->from_user_id === $user->id;
    }
}
