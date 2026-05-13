<?php

namespace App\Actions\Admin\Permissions;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CreatePermissionAction
{
    public function handle(string $name, string $module, string $guardName = 'web'): Permission
    {
        $permission = Permission::create([
            'name'       => $name,
            'module'     => $module,
            'guard_name' => $guardName,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $permission;
    }
}
