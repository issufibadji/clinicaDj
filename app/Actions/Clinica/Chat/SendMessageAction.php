<?php

namespace App\Actions\Clinica\Chat;

use App\Models\ChatMessage;
use App\Models\User;

class SendMessageAction
{
    public function handle(User $from, User $to, string $body): ChatMessage
    {
        return ChatMessage::create([
            'from_user_id' => $from->id,
            'to_user_id'   => $to->id,
            'body'         => $body,
        ]);
    }
}
