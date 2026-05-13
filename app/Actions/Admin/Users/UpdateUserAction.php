<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class UpdateUserAction
{
    public function handle(User $user, array $data, string $roleName): void
    {
        $payload = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'is_active' => $data['is_active'] ?? $user->is_active,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        $user->syncRoles([$roleName]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (range(1, 4) as $level) {
            Cache::forget("sidebar.menu.level.{$level}");
        }
    }
}
