<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Recuperar senha
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Informe seu e-mail e enviaremos um link para redefinir sua senha.
        </p>
    </div>

    {{-- Status (link enviado) --}}
    @if(session('status'))
        <div class="mb-5 flex items-center gap-3 p-4 rounded-xl
                    bg-emerald-50 dark:bg-emerald-900/30
                    border border-emerald-200 dark:border-emerald-700
                    text-emerald-700 dark:text-emerald-400 text-sm">
            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-5">

        {{-- E-mail --}}
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                E-mail
            </label>
            <input wire:model="email"
                   id="email" type="email" name="email"
                   required autofocus autocomplete="username"
                   placeholder="seu@email.com"
                   class="input" />
            @error('email')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Botão --}}
        <button type="submit"
                class="btn-primary w-full flex items-center justify-center gap-2 py-2.5"
                wire:loading.attr="disabled">
            <span wire:loading.remove>Enviar link de recuperação</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Enviando...
            </span>
        </button>

        {{-- Voltar ao login --}}
        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('login') }}" wire:navigate
               class="font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                ← Voltar ao login
            </a>
        </p>

    </form>
</div>
