<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Verificar e-mail
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Verifique seu endereço de e-mail para continuar.
        </p>
    </div>

    {{-- Ícone --}}
    <div class="flex justify-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
            <x-heroicon-o-envelope class="w-7 h-7 text-amber-500 dark:text-amber-400" />
        </div>
    </div>

    <p class="text-sm text-slate-600 dark:text-slate-400 text-center mb-6 leading-relaxed">
        Enviamos um link de verificação para o seu e-mail.<br>
        Clique no link recebido para ativar sua conta.
    </p>

    {{-- Status: link reenviado --}}
    @if(session('status') === 'verification-link-sent')
        <div class="mb-5 flex items-center gap-3 p-4 rounded-xl
                    bg-emerald-50 dark:bg-emerald-900/30
                    border border-emerald-200 dark:border-emerald-700
                    text-emerald-700 dark:text-emerald-400 text-sm">
            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
            Novo link de verificação enviado com sucesso!
        </div>
    @endif

    <div class="space-y-3">
        {{-- Reenviar --}}
        <button wire:click="sendVerification"
                class="btn-primary w-full flex items-center justify-center gap-2 py-2.5"
                wire:loading.attr="disabled">
            <span wire:loading.remove>Reenviar e-mail de verificação</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Enviando...
            </span>
        </button>

        {{-- Sair --}}
        <button wire:click="logout"
                class="w-full py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400
                       hover:text-red-600 dark:hover:text-red-400 transition-colors">
            Sair da conta
        </button>
    </div>
</div>
