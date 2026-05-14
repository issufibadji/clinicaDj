<?php

namespace App\Http\Middleware;

use App\Actions\Impersonation\IncrementImpersonationActions;
use App\Actions\Impersonation\StopImpersonation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckImpersonationTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('impersonating')) {
            return $next($request);
        }

        $expires = session('impersonation_expires');

        if ($expires && now()->timestamp > $expires) {
            app(StopImpersonation::class)->handle('timeout');

            session()->flash('warning', __('A impersonação expirou automaticamente após 2 horas.'));

            return redirect()->route('admin.usuarios.index');
        }

        // Conta ações durante impersonação (ignora requisições Livewire internas)
        if (! $request->hasHeader('X-Livewire') && $request->method() !== 'GET') {
            app(IncrementImpersonationActions::class)->handle();
        }

        return $next($request);
    }
}
