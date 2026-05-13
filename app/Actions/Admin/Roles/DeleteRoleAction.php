<?php

namespace App\Actions\Admin\Roles;

use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DeleteRoleAction
{
    public function handle(Role $role): void
    {
        if ($role->name === 'admin') {
            throw ValidationException::withMessages([
                'role' => 'O papel admin não pode ser excluído.',
            ]);
        }

        if ($role->users()->count() > 0) {
            throw ValidationException::withMessages([
                'role' => 'Não é possível excluir: existem usuários vinculados a este papel.',
            ]);
        }

        $role->syncPermissions([]);
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (range(1, 4) as $level) {
            Cache::forget("sidebar.menu.level.{$level}");
        }
    }
}
