<?php

namespace App\Actions\Admin\Profiles;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SwitchActiveProfile
{
    public function handle(User $user, string $profileId): void
    {
        $profile = $user->profiles()
            ->where('id', $profileId)
            ->where('is_active', true)
            ->first();

        if (! $profile) {
            throw new AuthorizationException(__('Perfil não encontrado ou inativo.'));
        }

        $user->switchToProfile($profile->id);
    }
}
