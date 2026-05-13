<?php

namespace App\Actions\Profile;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class EnableTwoFactorAction
{
    public function __construct(private readonly Google2FA $google2fa) {}

    public function handle(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();

        $user->update(['two_factor_secret' => encrypt($secret)]);

        $qrUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return compact('secret', 'qrUrl');
    }
}
