<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function handle(array $data, string $roleName): User
    {
        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'is_active'         => $data['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($roleName);

        return $user;
    }
}
