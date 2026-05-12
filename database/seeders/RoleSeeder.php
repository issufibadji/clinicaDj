<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',          'level' => 1, 'guard_name' => 'web'],
            ['name' => 'medico',         'level' => 2, 'guard_name' => 'web'],
            ['name' => 'recepcionista',  'level' => 3, 'guard_name' => 'web'],
            ['name' => 'financeiro',     'level' => 4, 'guard_name' => 'web'],
        ];

        foreach ($roles as $data) {
            Role::updateOrCreate(
                ['name' => $data['name'], 'guard_name' => $data['guard_name']],
                ['level' => $data['level']]
            );
        }
    }
}
