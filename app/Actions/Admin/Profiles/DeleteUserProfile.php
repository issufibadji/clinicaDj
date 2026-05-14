<?php

namespace App\Actions\Admin\Profiles;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteUserProfile
{
    public function handle(User $user, string $profileId): void
    {
        $profile = $user->profiles()->where('id', $profileId)->firstOrFail();

        $activeCount = $user->profiles()->where('is_active', true)->count();

        if ($activeCount <= 1 && $profile->is_active) {
            throw ValidationException::withMessages([
                'profile' => __('Não é possível remover o único perfil ativo do usuário.'),
            ]);
        }

        if ($user->active_profile_id === $profile->id) {
            throw ValidationException::withMessages([
                'profile' => __('Não é possível remover o perfil atualmente em uso. Troque de perfil primeiro.'),
            ]);
        }

        $profile->update(['is_active' => false, 'is_default' => false]);

        if (! $user->profiles()->where('is_default', true)->where('is_active', true)->exists()) {
            $user->profiles()->where('is_active', true)->first()?->update(['is_default' => true]);
        }
    }
}
