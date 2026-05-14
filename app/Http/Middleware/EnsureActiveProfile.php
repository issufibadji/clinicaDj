<?php

namespace App\Http\Middleware;

use App\Actions\Admin\Profiles\SwitchActiveProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Rotas de seleção de perfil são passadas sem checar
        if ($request->routeIs('auth.select-profile')) {
            return $next($request);
        }

        $profileId = session('active_profile_id') ?? $user->active_profile_id;

        if ($profileId) {
            $valid = $user->profiles()->where('id', $profileId)->where('is_active', true)->exists();
            if ($valid) {
                // Garante que active_profile_id está na sessão
                session(['active_profile_id' => $profileId]);
                return $next($request);
            }
        }

        // Nenhum perfil ativo na sessão — tenta usar o padrão
        $default = $user->profiles()->where('is_default', true)->where('is_active', true)->first()
            ?? $user->profiles()->where('is_active', true)->first();

        if ($default) {
            app(SwitchActiveProfile::class)->handle($user, $default->id);
            return $next($request);
        }

        // Usuário sem nenhum perfil ativo → redireciona para seleção
        if ($user->profiles()->where('is_active', true)->count() > 1) {
            return redirect()->route('auth.select-profile');
        }

        return $next($request);
    }
}
