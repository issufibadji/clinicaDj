<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class ToggleUserStatusAction
{
    public function handle(User $actor, User $target): void
    {
        if ($actor->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode desativar sua própria conta.',
            ]);
        }

        $target->update(['is_active' => ! $target->is_active]);
    }
}
