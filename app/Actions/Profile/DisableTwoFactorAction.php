<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class DisableTwoFactorAction
{
    public function __construct(private readonly Google2FA $google2fa) {}

    public function handle(User $user, string $password, string $code): void
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'disablePassword' => 'Senha incorreta.',
            ]);
        }

        $secret = decrypt($user->two_factor_secret);

        if (! $this->google2fa->verifyKey($secret, $code)) {
            throw ValidationException::withMessages([
                'disableCode' => 'Código 2FA inválido.',
            ]);
        }

        $user->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);
    }
}
