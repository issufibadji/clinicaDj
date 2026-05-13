<?php

namespace App\Actions\Admin\UserRoles;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class AssignUserRolesAction
{
    public function handle(User $user, array $roleNames): void
    {
        $user->syncRoles($roleNames);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (range(1, 4) as $level) {
            Cache::forget("sidebar.menu.level.{$level}");
        }
    }
}
