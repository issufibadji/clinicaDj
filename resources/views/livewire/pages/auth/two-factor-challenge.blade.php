<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use PragmaRX\Google2FA\Google2FA;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';
    public string $recovery_code = '';
    public bool $usingRecovery = false;

    public function toggleRecovery(): void
    {
        $this->usingRecovery = ! $this->usingRecovery;
        $this->code = '';
        $this->recovery_code = '';
    }

    public function verify(): void
    {
        $user = Auth::user();

        if ($this->usingRecovery) {
            $this->validate(['recovery_code' => ['required', 'string']]);

            if (! $user->validateAndConsumeRecoveryCode($this->recovery_code)) {
                throw ValidationException::withMessages([
                    'recovery_code' => 'Código de recuperação inválido.',
                ]);
            }
        } else {
            $this->validate(['code' => ['required', 'string', 'digits:6']]);

            $engine = app(Google2FA::class);

            $valid = $engine->verifyKey(
                decrypt($user->two_factor_secret),
                $this->code
            );

            if (! $valid) {
                throw ValidationException::withMessages([
                    'code' => 'Código de verificação inválido ou expirado.',
                ]);
            }
        }

        session(['auth.2fa_verified' => true]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Verificação em duas etapas
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            @if($usingRecovery)
                Informe um código de recuperação da sua conta.
            @else
                Informe o código gerado pelo seu aplicativo autenticador.
            @endif
        </p>
    </div>

    {{-- Ícone --}}
    <div class="flex justify-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center">
            <x-heroicon-o-shield-check class="w-7 h-7 text-primary-600 dark:text-primary-400" />
        </div>
    </div>

    <form wire:submit="verify" class="space-y-5">

        @if(! $usingRecovery)
            {{-- Código TOTP --}}
            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Código de 6 dígitos
                </label>
                <input wire:model="code"
                       id="code" type="text" name="code"
                       inputmode="numeric" pattern="[0-9]*"
                       maxlength="6" autocomplete="one-time-code"
                       required autofocus
                       placeholder="000000"
                       class="input text-center text-xl tracking-[0.5em] font-mono" />
                @error('code')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ $message }}
                    </p>
                @enderror
            </div>
        @else
            {{-- Código de recuperação --}}
            <div>
                <label for="recovery_code" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Código de recuperação
                </label>
                <input wire:model="recovery_code"
                       id="recovery_code" type="text" name="recovery_code"
                       autocomplete="off" required autofocus
                       placeholder="xxxx-xxxx-xxxx-xxxx"
                       class="input font-mono" />
                @error('recovery_code')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ $message }}
                    </p>
                @enderror
            </div>
        @endif

        {{-- Botão verificar --}}
        <button type="submit"
                class="btn-primary w-full flex items-center justify-center gap-2 py-2.5"
                wire:loading.attr="disabled">
            <span wire:loading.remove>Verificar</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Verificando...
            </span>
        </button>

        {{-- Alternar método --}}
        <div class="text-center">
            <button type="button" wire:click="toggleRecovery"
                    class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                @if($usingRecovery)
                    Usar código do autenticador
                @else
                    Usar código de recuperação
                @endif
            </button>
        </div>

    </form>
</div>
