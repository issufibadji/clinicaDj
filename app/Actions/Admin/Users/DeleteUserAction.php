<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteUserAction
{
    public function handle(User $actor, User $target): void
    {
        if ($actor->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode excluir sua própria conta.',
            ]);
        }

        $target->delete();
    }
}
