<?php

namespace App\Actions\Admin\Roles;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateRoleAction
{
    public function handle(string $name, int $level, array $permissionIds = []): Role
    {
        $role = Role::create(['name' => $name, 'guard_name' => 'web', 'level' => $level]);

        if ($permissionIds) {
            $role->syncPermissions($permissionIds);
        }

        $this->invalidateCache();

        return $role;
    }

    private function invalidateCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (range(1, 4) as $level) {
            Cache::forget("sidebar.menu.level.{$level}");
        }
    }
}
