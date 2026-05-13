<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetUserPasswordAction
{
    public function handle(User $user): string
    {
        $plain = Str::password(16);

        $user->update(['password' => Hash::make($plain)]);

        return $plain;
    }
}
