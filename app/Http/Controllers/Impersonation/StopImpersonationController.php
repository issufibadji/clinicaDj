<?php

namespace App\Http\Controllers\Impersonation;

use App\Actions\Impersonation\StopImpersonation;
use App\Exceptions\ImpersonationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StopImpersonationController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        try {
            app(StopImpersonation::class)->handle('manual');
        } catch (ImpersonationException $e) {
            return redirect()->route('dashboard');
        }

        session()->flash('success', __('Impersonação encerrada. Você voltou para sua conta.'));

        return redirect()->route('admin.usuarios.index');
    }
}
