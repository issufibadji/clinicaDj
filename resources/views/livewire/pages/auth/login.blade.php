<?php

use App\Actions\Admin\Profiles\SwitchActiveProfile;
use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();

        $user = auth()->user();
        $activeProfiles = $user->profiles()->where('is_active', true)->count();

        // Usuário com múltiplos perfis → tela de seleção
        if ($activeProfiles > 1 && ! $user->active_profile_id) {
            $this->redirect(route('auth.select-profile'), navigate: true);
            return;
        }

        // Ativa perfil padrão automaticamente
        if ($activeProfiles >= 1 && ! $user->active_profile_id) {
            $default = $user->profiles()->where('is_default', true)->where('is_active', true)->first()
                ?? $user->profiles()->where('is_active', true)->first();

            if ($default) {
                app(SwitchActiveProfile::class)->handle($user, $default->id);
            }
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Entrar na sua conta
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Bem-vindo de volta. Informe suas credenciais.
        </p>
    </div>

    {{-- Status de sessão (ex: senha redefinida) --}}
    @if(session('status'))
        <div class="mb-5 flex items-center gap-3 p-4 rounded-xl
                    bg-emerald-50 dark:bg-emerald-900/30
                    border border-emerald-200 dark:border-emerald-700
                    text-emerald-700 dark:text-emerald-400 text-sm">
            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">

        {{-- E-mail --}}
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                E-mail
            </label>
            <input wire:model="form.email"
                   id="email" type="email" name="email"
                   required autofocus autocomplete="username"
                   placeholder="seu@email.com"
                   class="input" />
            @error('form.email')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Senha com toggle visibilidade --}}
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Senha
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>
            <div class="relative">
                <input wire:model="form.password"
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
            @error('form.password')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Lembrar-me --}}
        <div class="flex items-center gap-2">
            <input wire:model="form.remember" id="remember" type="checkbox"
                   class="w-4 h-4 rounded border-slate-300 dark:border-slate-600
                          text-primary-600 focus:ring-primary-500 focus:ring-offset-0
                          bg-white dark:bg-slate-700 cursor-pointer" />
            <label for="remember" class="text-sm text-slate-600 dark:text-slate-400 cursor-pointer select-none">
                Lembrar-me por 30 dias
            </label>
        </div>

        {{-- Botão --}}
        <button type="submit"
                class="btn-primary w-full flex items-center justify-center gap-2 py-2.5"
                wire:loading.attr="disabled">
            <span wire:loading.remove>Entrar</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Entrando...
            </span>
        </button>

    </form>
</div>
