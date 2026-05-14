<?php

use App\Actions\Impersonation\StartImpersonation;
use App\Exceptions\ImpersonationException;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public bool    $open         = false;
    public string  $targetUserId = '';
    public string  $notes        = '';

    #[On('open-impersonation-modal')]
    public function openModal(string $userId): void
    {
        $this->targetUserId = $userId;
        $this->notes        = '';
        $this->open         = true;
    }

    public function confirm(): void
    {
        $admin  = auth()->user();
        $target = User::findOrFail($this->targetUserId);

        try {
            app(StartImpersonation::class)->handle($admin, $target, $this->notes ?: null);
        } catch (ImpersonationException $e) {
            session()->flash('error', $e->getMessage());
            $this->open = false;
            return;
        }

        $this->open = false;
        $this->redirect(route('dashboard'), navigate: false);
    }

    public function cancel(): void
    {
        $this->open = false;
        $this->reset('targetUserId', 'notes');
    }

    public function with(): array
    {
        $target = $this->targetUserId ? User::with('roles')->find($this->targetUserId) : null;

        return ['targetUser' => $target];
    }
}; ?>

<div>
@if($open && $targetUser)
<div class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     x-data x-on:keydown.escape.window="$wire.cancel()">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
         wire:click="cancel"></div>

    {{-- Modal --}}
    <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                border border-slate-200 dark:border-slate-700 overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700
                    bg-amber-50 dark:bg-amber-900/20">
            <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-eye class="w-5 h-5 text-amber-600 dark:text-amber-400" />
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                    {{ __('Entrar como usuário') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ __('Acesso temporário de suporte') }}
                </p>
            </div>
            <button wire:click="cancel" class="ml-auto p-1.5 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
            </button>
        </div>

        <div class="p-6 space-y-5">
            {{-- Card do usuário alvo --}}
            <div class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50
                        border border-slate-200 dark:border-slate-600">
                <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center
                            text-white text-sm font-bold flex-shrink-0">
                    {{ $targetUser->initials() }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
                        {{ $targetUser->name }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">
                        {{ $targetUser->getRoleNames()->first() ?? '—' }}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate">
                        {{ $targetUser->email }}
                    </p>
                </div>
            </div>

            {{-- Motivo --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ __('Motivo (opcional)') }}
                </label>
                <textarea wire:model="notes" rows="2"
                          placeholder="{{ __('Ex: Verificar bug no agendamento reportado pelo usuário') }}"
                          class="input resize-none text-sm"></textarea>
            </div>

            {{-- Aviso --}}
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20
                        border border-amber-200 dark:border-amber-800/40 text-xs text-amber-700 dark:text-amber-400">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                <div class="space-y-0.5">
                    <p>{{ __('Esta ação será registrada em auditoria com data, hora e IP.') }}</p>
                    <p>{{ __('Ações destrutivas estarão bloqueadas durante a sessão.') }}</p>
                    <p>{{ __('A sessão expira automaticamente em 2 horas.') }}</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
            <button wire:click="cancel" class="btn-secondary text-sm py-1.5 px-4">
                {{ __('Cancelar') }}
            </button>
            <button wire:click="confirm"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white
                           font-semibold text-sm py-1.5 px-4 rounded-lg transition-colors">
                <span wire:loading.remove wire:target="confirm">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 inline" />
                    {{ __('Entrar como :name', ['name' => explode(' ', $targetUser->name)[0]]) }}
                </span>
                <span wire:loading wire:target="confirm">{{ __('Entrando...') }}</span>
            </button>
        </div>
    </div>
</div>
@endif
</div>
