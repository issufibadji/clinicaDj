<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'admin' => [
                // tudo
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete',
                'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
                'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',
                'payments.view', 'payments.create',
                'reports.view', 'reports.export',
                'rooms.view', 'rooms.manage',
                'departments.view', 'departments.manage',
                'insurance.view', 'insurance.manage',
                'events.view', 'events.create', 'events.edit', 'events.delete',
                'chat.view', 'chat.send',
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view', 'roles.manage',
                'permissions.view', 'permissions.manage',
                'system.audit', 'system.menus', 'system.settings',
            ],
            'medico' => [
                'appointments.view',
                'patients.view', 'patients.edit',
                'doctors.view',
                'events.view',
                'chat.view', 'chat.send',
            ],
            'recepcionista' => [
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete',
                'patients.view', 'patients.create', 'patients.edit',
                'doctors.view',
                'payments.view', 'payments.create',
                'rooms.view',
                'events.view',
                'chat.view', 'chat.send',
            ],
            'financeiro' => [
                'appointments.view',
                'payments.view', 'payments.create',
                'reports.view', 'reports.export',
                'insurance.view', 'insurance.manage',
                'departments.view',
                'chat.view', 'chat.send',
            ],
        ];

        foreach ($map as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($perms);
            }
        }
    }
}
