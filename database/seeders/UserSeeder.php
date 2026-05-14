<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Administrador',
                'email'    => 'admin@clinica.com',
                'roles'    => ['admin'],
                'profiles' => [
                    ['role' => 'admin', 'display_name' => 'Admin — Clínica JM', 'color' => '#6366F1', 'is_default' => true],
                ],
            ],
            [
                'name'     => 'Dr. João Médico',
                'email'    => 'medico@clinica.com',
                'roles'    => ['medico', 'recepcionista'],
                'profiles' => [
                    ['role' => 'medico',        'display_name' => 'Dr. João — Clínica JM', 'color' => '#10B981', 'is_default' => true],
                    ['role' => 'recepcionista', 'display_name' => 'Recepção — Dr. João',    'color' => '#3B82F6', 'is_default' => false],
                ],
            ],
            [
                'name'     => 'Maria Recepção',
                'email'    => 'recepcao@clinica.com',
                'roles'    => ['recepcionista'],
                'profiles' => [
                    ['role' => 'recepcionista', 'display_name' => 'Maria — Recepção', 'color' => '#3B82F6', 'is_default' => true],
                ],
            ],
            [
                'name'     => 'Carlos Financeiro',
                'email'    => 'financeiro@clinica.com',
                'roles'    => ['financeiro', 'medico'],
                'profiles' => [
                    ['role' => 'financeiro', 'display_name' => 'Carlos — Financeiro', 'color' => '#F59E0B', 'is_default' => true],
                    ['role' => 'medico',     'display_name' => 'Carlos — Médico',     'color' => '#10B981', 'is_default' => false],
                ],
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

            $user->syncRoles($data['roles']);

            foreach ($data['profiles'] as $profileData) {
                $role = Role::where('name', $profileData['role'])->first();

                if (! $role) {
                    continue;
                }

                if ($user->hasProfile($role->id)) {
                    continue;
                }

                $profile = UserProfile::create([
                    'user_id'      => $user->id,
                    'role_id'      => $role->id,
                    'display_name' => $profileData['display_name'],
                    'color'        => $profileData['color'],
                    'is_default'   => $profileData['is_default'],
                    'is_active'    => true,
                ]);

                if ($profileData['is_default']) {
                    $user->updateQuietly(['active_profile_id' => $profile->id]);
                    session(['active_profile_id' => $profile->id]);
                }
            }
        }
    }
}
