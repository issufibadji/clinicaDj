<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'  => 'Administrador',
                'email' => 'admin@clinica.com',
                'role'  => 'admin',
            ],
            [
                'name'  => 'Dr. João Médico',
                'email' => 'medico@clinica.com',
                'role'  => 'medico',
            ],
            [
                'name'  => 'Maria Recepção',
                'email' => 'recepcao@clinica.com',
                'role'  => 'recepcionista',
            ],
            [
                'name'  => 'Carlos Financeiro',
                'email' => 'financeiro@clinica.com',
                'role'  => 'financeiro',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active'         => true,
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
