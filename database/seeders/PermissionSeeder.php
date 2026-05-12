<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // appointments
            ['name' => 'appointments.view',   'module' => 'appointments'],
            ['name' => 'appointments.create', 'module' => 'appointments'],
            ['name' => 'appointments.edit',   'module' => 'appointments'],
            ['name' => 'appointments.delete', 'module' => 'appointments'],
            // patients
            ['name' => 'patients.view',   'module' => 'patients'],
            ['name' => 'patients.create', 'module' => 'patients'],
            ['name' => 'patients.edit',   'module' => 'patients'],
            ['name' => 'patients.delete', 'module' => 'patients'],
            // doctors
            ['name' => 'doctors.view',   'module' => 'doctors'],
            ['name' => 'doctors.create', 'module' => 'doctors'],
            ['name' => 'doctors.edit',   'module' => 'doctors'],
            ['name' => 'doctors.delete', 'module' => 'doctors'],
            // payments
            ['name' => 'payments.view',   'module' => 'payments'],
            ['name' => 'payments.create', 'module' => 'payments'],
            // reports
            ['name' => 'reports.view',   'module' => 'reports'],
            ['name' => 'reports.export', 'module' => 'reports'],
            // rooms
            ['name' => 'rooms.view',   'module' => 'rooms'],
            ['name' => 'rooms.manage', 'module' => 'rooms'],
            // departments
            ['name' => 'departments.view',   'module' => 'departments'],
            ['name' => 'departments.manage', 'module' => 'departments'],
            // insurance
            ['name' => 'insurance.view',   'module' => 'insurance'],
            ['name' => 'insurance.manage', 'module' => 'insurance'],
            // events
            ['name' => 'events.view',   'module' => 'events'],
            ['name' => 'events.create', 'module' => 'events'],
            ['name' => 'events.edit',   'module' => 'events'],
            ['name' => 'events.delete', 'module' => 'events'],
            // chat
            ['name' => 'chat.view', 'module' => 'chat'],
            ['name' => 'chat.send', 'module' => 'chat'],
            // users (admin)
            ['name' => 'users.view',   'module' => 'users'],
            ['name' => 'users.create', 'module' => 'users'],
            ['name' => 'users.edit',   'module' => 'users'],
            ['name' => 'users.delete', 'module' => 'users'],
            // roles
            ['name' => 'roles.view',   'module' => 'roles'],
            ['name' => 'roles.manage', 'module' => 'roles'],
            // permissions
            ['name' => 'permissions.view',   'module' => 'permissions'],
            ['name' => 'permissions.manage', 'module' => 'permissions'],
            // system
            ['name' => 'system.audit',    'module' => 'system'],
            ['name' => 'system.menus',    'module' => 'system'],
            ['name' => 'system.settings', 'module' => 'system'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['module' => $perm['module']]
            );
        }
    }
}
