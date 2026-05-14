<?php

namespace App\Http\Controllers\Impersonation;

use App\Actions\Impersonation\StartImpersonation;
use App\Exceptions\ImpersonationException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StartImpersonationController extends Controller
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        try {
            app(StartImpersonation::class)->handle(
                admin: auth()->user(),
                target: $user,
                notes: $request->input('notes'),
            );
        } catch (ImpersonationException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        session()->flash('success', __('Você está acessando como :name.', ['name' => $user->name]));

        return redirect()->route('dashboard');
    }
}
