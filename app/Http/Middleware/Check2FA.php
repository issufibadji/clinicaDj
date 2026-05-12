<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Check2FA
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user &&
            $user->hasTwoFactorEnabled() &&
            ! session('auth.2fa_verified') &&
            ! $request->routeIs('two-factor.challenge')
        ) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
