<?php

namespace App\Actions\Admin\Roles;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UpdateRoleAction
{
    public function handle(Role $role, int $level, array $permissionIds = []): void
    {
        $data = ['level' => $level];

        // Protege o nome do papel admin contra edição
        if ($role->name !== 'admin') {
            // name só pode mudar se passado explicitamente
        }

        $role->update($data);
        $role->syncPermissions($permissionIds);

        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (range(1, 4) as $level) {
            Cache::forget("sidebar.menu.level.{$level}");
        }
    }
}
