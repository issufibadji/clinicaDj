<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateRecoveryCodesAction
{
    public function handle(User $user): array
    {
        $plainCodes = collect(range(1, 8))->map(function () {
            return strtoupper(Str::random(5)) . '-' . strtoupper(Str::random(5));
        })->all();

        $hashed = array_map(fn($code) => Hash::make($code), $plainCodes);
        $user->storeRecoveryCodes($hashed);

        return $plainCodes;
    }
}
