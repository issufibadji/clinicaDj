<?php

namespace App\Actions\Admin\Permissions;

use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DeletePermissionAction
{
    public function handle(Permission $permission): void
    {
        if ($permission->roles()->count() > 0) {
            throw ValidationException::withMessages([
                'permission' => 'Não é possível excluir: esta permissão está vinculada a papéis.',
            ]);
        }

        $permission->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
