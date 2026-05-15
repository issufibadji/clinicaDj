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

{{-- Form sempre sobre fundo claro (lavanda) — sem dark: variants nos inputs --}}
<div>
    {{-- Cabeçalho --}}
    <div class="login-form-field mb-7">
        <h2 class="text-xl font-bold text-slate-800">
            Entrar na sua conta
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Bem-vindo de volta. Informe suas credenciais.
        </p>
    </div>

    {{-- Status de sessão (ex: senha redefinida) --}}
    @if(session('status'))
        <div class="mb-5 flex items-center gap-3 p-3.5 rounded-xl
                    bg-emerald-50 border border-emerald-200
                    text-emerald-700 text-sm">
            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
            {{ session('status') }}
        </div>
    @endif

    {{-- Resumo de erros --}}
    @if($errors->any())
        <div class="flex items-start gap-3 p-3.5 rounded-xl
                    bg-red-50 border border-red-200 mb-5">
            <x-heroicon-o-exclamation-circle class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0"/>
            <p class="text-red-600 text-xs leading-relaxed">{{ $errors->first() }}</p>
        </div>
    @endif

    <form wire:submit="login" class="space-y-4">

        {{-- E-mail --}}
        <div class="login-form-field">
            <label for="email" class="block text-xs font-semibold text-slate-600 mb-1.5 tracking-wide uppercase">
                E-mail
            </label>
            <div class="relative">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <x-heroicon-o-envelope class="w-4 h-4"/>
                </div>
                <input wire:model="form.email"
                       id="email" type="email" name="email"
                       required autofocus autocomplete="username"
                       placeholder="seu@email.com"
                       class="w-full rounded-xl pl-10 pr-4 py-2.5 text-sm
                              bg-white/70 border border-slate-200/80
                              text-slate-800 placeholder-slate-400
                              focus:outline-none focus:border-primary-400
                              focus:ring-2 focus:ring-primary-400/20
                              focus:bg-white transition-all duration-200" />
            </div>
            @error('form.email')
                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Senha --}}
        <div class="login-form-field" x-data="{ show: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-600 tracking-wide uppercase">
                    Senha
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="text-[11px] text-primary-600 hover:text-primary-700 font-medium">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <x-heroicon-o-lock-closed class="w-4 h-4"/>
                </div>
                <input wire:model="form.password"
                       id="password" :type="show ? 'text' : 'password'"
                       name="password" required autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full rounded-xl pl-10 pr-10 py-2.5 text-sm
                              bg-white/70 border border-slate-200/80
                              text-slate-800 placeholder-slate-400
                              focus:outline-none focus:border-primary-400
                              focus:ring-2 focus:ring-primary-400/20
                              focus:bg-white transition-all duration-200" />
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
                    <x-heroicon-o-eye      x-show="!show" class="w-4 h-4" />
                    <x-heroicon-o-eye-slash x-show="show" class="w-4 h-4" style="display:none" />
                </button>
            </div>
            @error('form.password')
                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Lembrar-me --}}
        <div class="flex items-center gap-2">
            <input wire:model="form.remember" id="remember" type="checkbox"
                   class="w-4 h-4 rounded border-slate-300 text-primary-600
                          focus:ring-primary-500/30 focus:ring-offset-0
                          bg-white cursor-pointer" />
            <label for="remember" class="text-sm text-slate-500 cursor-pointer select-none">
                Lembrar-me por 30 dias
            </label>
        </div>

        {{-- Botão Entrar --}}
        <div class="login-btn pt-1">
            <button type="submit"
                    class="w-full relative overflow-hidden rounded-xl py-3
                           bg-gradient-to-r from-green-500 to-emerald-600
                           hover:from-green-400 hover:to-emerald-500
                           text-white font-semibold text-sm tracking-wide
                           shadow-lg shadow-green-500/30
                           hover:shadow-green-500/50 hover:shadow-xl
                           hover:-translate-y-0.5
                           transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                           focus:ring-offset-white/50
                           disabled:opacity-60 disabled:cursor-not-allowed
                           group"
                    wire:loading.attr="disabled">

                <span wire:loading.remove class="flex items-center justify-center gap-2">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4"/>
                    Entrar
                </span>

                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/>
                    </svg>
                    Entrando...
                </span>

                {{-- Shimmer no hover --}}
                <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full
                            bg-gradient-to-r from-transparent via-white/15 to-transparent
                            transition-transform duration-700 pointer-events-none"></div>
            </button>

            {{-- Segurança --}}
            <div class="flex items-center justify-center gap-1.5 mt-3 text-slate-400 text-[11px]">
                <x-heroicon-o-lock-closed class="w-3 h-3 text-green-500/60"/>
                <span>Conexão segura · Criptografia SSL</span>
            </div>
        </div>

    </form>
</div>
