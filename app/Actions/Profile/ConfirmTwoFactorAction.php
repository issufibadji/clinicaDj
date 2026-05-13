<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class ConfirmTwoFactorAction
{
    public function __construct(
        private readonly Google2FA $google2fa,
        private readonly GenerateRecoveryCodesAction $generateCodes,
    ) {}

    public function handle(User $user, string $code): array
    {
        $secret = decrypt($user->two_factor_secret);

        if (! $this->google2fa->verifyKey($secret, $code)) {
            throw ValidationException::withMessages([
                'confirmCode' => 'Código inválido. Verifique o seu autenticador.',
            ]);
        }

        $user->update(['two_factor_confirmed_at' => now()]);

        return $this->generateCodes->handle($user);
    }
}
