<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email'    => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Área segura
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Confirme sua senha para acessar esta área protegida.
        </p>
    </div>

    {{-- Ícone de cadeado --}}
    <div class="flex justify-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center">
            <x-heroicon-o-lock-closed class="w-7 h-7 text-primary-600 dark:text-primary-400" />
        </div>
    </div>

    <form wire:submit="confirmPassword" class="space-y-5">

        {{-- Senha --}}
        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Senha atual
            </label>
            <div class="relative">
                <input wire:model="password"
                       id="password" :type="show ? 'text' : 'password'"
                       name="password" required autocomplete="current-password"
                       placeholder="••••••••"
                       class="input pr-10" />
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <x-heroicon-o-eye x-show="!show" class="w-4 h-4" />
                    <x-heroicon-o-eye-slash x-show="show" class="w-4 h-4" style="display:none" />
                </button>
            </div>
            @error('password')
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
            <span wire:loading.remove>Confirmar e continuar</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Verificando...
            </span>
        </button>

    </form>
</div>
