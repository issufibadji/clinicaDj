<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token'    => ['required'],
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password'       => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Redefinir senha
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Escolha uma nova senha segura para sua conta.
        </p>
    </div>

    <form wire:submit="resetPassword" class="space-y-5">

        {{-- E-mail (somente leitura) --}}
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                E-mail
            </label>
            <input wire:model="email"
                   id="email" type="email" name="email"
                   required readonly autocomplete="username"
                   class="input bg-slate-50 dark:bg-slate-800 cursor-not-allowed" />
            @error('email')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Nova senha --}}
        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Nova senha
            </label>
            <div class="relative">
                <input wire:model="password"
                       id="password" :type="show ? 'text' : 'password'"
                       name="password" required autocomplete="new-password"
                       placeholder="Mínimo 8 caracteres"
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

        {{-- Confirmar nova senha --}}
        <div x-data="{ show2: false }">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Confirmar nova senha
            </label>
            <div class="relative">
                <input wire:model="password_confirmation"
                       id="password_confirmation" :type="show2 ? 'text' : 'password'"
                       name="password_confirmation" required autocomplete="new-password"
                       placeholder="Repita a nova senha"
                       class="input pr-10" />
                <button type="button" @click="show2 = !show2"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <x-heroicon-o-eye x-show="!show2" class="w-4 h-4" />
                    <x-heroicon-o-eye-slash x-show="show2" class="w-4 h-4" style="display:none" />
                </button>
            </div>
            @error('password_confirmation')
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
            <span wire:loading.remove>Redefinir senha</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Salvando...
            </span>
        </button>

    </form>
</div>
