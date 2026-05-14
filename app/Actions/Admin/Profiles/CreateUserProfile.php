<?php

namespace App\Actions\Admin\Profiles;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CreateUserProfile
{
    public function handle(User $user, array $data): UserProfile
    {
        $role = Role::findOrFail($data['role_id']);

        if ($user->hasProfile($role->id)) {
            throw ValidationException::withMessages([
                'role_id' => __('O usuário já possui um perfil com este papel.'),
            ]);
        }

        $isFirst = $user->profiles()->where('is_active', true)->count() === 0;

        $profile = $user->profiles()->create([
            'role_id'      => $role->id,
            'display_name' => $data['display_name'] ?? null,
            'avatar'       => $data['avatar'] ?? null,
            'color'        => $data['color'] ?? '#10B981',
            'is_default'   => $isFirst || ($data['is_default'] ?? false),
            'is_active'    => true,
            'settings'     => $data['settings'] ?? null,
        ]);

        if ($isFirst || ! $user->active_profile_id) {
            $user->switchToProfile($profile->id);
        }

        return $profile;
    }
}
