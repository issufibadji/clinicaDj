<?php

use App\Actions\Impersonation\StopImpersonation;
use App\Models\ImpersonationLog;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component
{
    public function stopImpersonation(): void
    {
        app(StopImpersonation::class)->handle('manual');

        session()->flash('success', __('Impersonação encerrada. Você voltou para sua conta.'));

        $this->redirect(route('admin.usuarios.index'), navigate: false);
    }

    public function with(): array
    {
        $logId  = session('impersonation_log_id');
        $log    = $logId ? ImpersonationLog::find($logId) : null;
        $target = $log ? User::find($log->target_id) : null;

        return [
            'log'       => $log,
            'target'    => $target,
            'expiresAt' => session('impersonation_expires'),
        ];
    }
}; ?>

<div x-data="{
        expiresAt: {{ $expiresAt ?? 0 }},
        timeLeft: 0,
        urgent: false,
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },
        tick() {
            const now = Math.floor(Date.now() / 1000);
            this.timeLeft = Math.max(0, this.expiresAt - now);
            this.urgent = this.timeLeft < 600;
        },
        format(s) {
            const h = Math.floor(s / 3600);
            const m = Math.floor((s % 3600) / 60);
            const sec = s % 60;
            if (h > 0) return `${h}h ${String(m).padStart(2,'0')}min`;
            return `${m}m ${String(sec).padStart(2,'0')}s`;
        }
    }"
     :class="urgent ? 'bg-red-600' : 'bg-amber-500'"
     class="w-full px-4 py-2.5 flex items-center gap-3 text-sm font-medium text-white shadow-md z-50 transition-colors duration-500">

    {{-- Ícone --}}
    <div class="flex-shrink-0">
        <x-heroicon-o-eye class="w-4 h-4" />
    </div>

    {{-- Texto principal --}}
    <div class="flex-1 flex items-center gap-2 flex-wrap">
        <span class="font-semibold">{{ __('Você está acessando como') }}</span>

        @if($target)
            <span class="inline-flex items-center gap-1.5 bg-white/20 rounded-full px-2.5 py-0.5 text-xs font-bold">
                {{ $target->initials() }}
                {{ $target->name }}
            </span>
            <span class="text-white/80 text-xs">
                — {{ $target->getRoleNames()->first() }}
            </span>
        @endif

        @if($log)
            <span class="text-white/70 text-xs hidden sm:inline">
                · {{ __('iniciado às :time', ['time' => $log->started_at->format('H:i')]) }}
            </span>
        @endif
    </div>

    {{-- Contador regressivo --}}
    <div class="flex-shrink-0 text-xs font-mono hidden sm:flex items-center gap-1"
         :class="urgent ? 'text-red-100 animate-pulse' : 'text-amber-100'">
        <x-heroicon-o-clock class="w-3.5 h-3.5" />
        <span x-text="format(timeLeft)"></span>
        <span class="text-white/60">{{ __('restantes') }}</span>
    </div>

    {{-- Botão sair --}}
    <button wire:click="stopImpersonation"
            wire:loading.attr="disabled"
            class="flex-shrink-0 flex items-center gap-1.5 bg-white/20 hover:bg-white/30
                   rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors">
        <span wire:loading.remove wire:target="stopImpersonation">
            <x-heroicon-o-arrow-left-on-rectangle class="w-3.5 h-3.5 inline" />
            {{ __('Sair da impersonação') }}
        </span>
        <span wire:loading wire:target="stopImpersonation">{{ __('Encerrando...') }}</span>
    </button>
</div>
