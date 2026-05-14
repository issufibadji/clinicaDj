<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDestructiveActionsOnImpersonation
{
    private const BLOCKED_ROUTES = [
        'profile.password',
        'profile.email',
        'profile.2fa.disable',
        'payments.destroy',
        'payments.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! session('impersonating')) {
            return $next($request);
        }

        // Bloqueia qualquer método DELETE
        if ($request->isMethod('DELETE')) {
            return $this->blocked($request);
        }

        // Bloqueia rotas específicas via POST/PATCH/PUT
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, self::BLOCKED_ROUTES, true)) {
            return $this->blocked($request);
        }

        return $next($request);
    }

    private function blocked(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('Ação bloqueada durante impersonação.')], 403);
        }

        session()->flash('error', __('Ação não permitida durante impersonação.'));

        return redirect()->back();
    }
}
