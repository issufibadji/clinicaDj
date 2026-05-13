<?php

use Livewire\Volt\Component;

new class extends Component {
    public bool $open = false;

    public function getListeners(): array
    {
        return ['notification-sent' => '$refresh'];
    }

    public function markRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function delete(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->delete();
    }

    public function with(): array
    {
        $notifications = auth()->user()->notifications()->latest()->take(20)->get();
        $unreadCount   = auth()->user()->unreadNotifications()->count();

        return compact('notifications', 'unreadCount');
    }
}; ?>

<div x-data="{ open: $wire.entangle('open') }"
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     class="relative"
     wire:poll.30s>

    {{-- Botão sino --}}
    <button @click="open = !open"
            class="icon-btn relative text-slate-500 dark:text-slate-400"
            title="{{ __('Notificações') }}">
        <x-heroicon-o-bell class="w-5 h-5" />
        @if($unreadCount > 0)
            <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center
                         rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-800
                         text-[9px] font-bold text-white leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 top-full mt-2 w-80
                bg-white dark:bg-slate-800 rounded-xl shadow-lg
                border border-slate-200 dark:border-slate-700 z-50 overflow-hidden"
         style="display: none;">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between px-4 py-3
                    border-b border-slate-100 dark:border-slate-700">
            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                {{ __('Notificações') }}
                @if($unreadCount > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold
                                 bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
                        {{ $unreadCount }}
                    </span>
                @endif
            </span>
            @if($unreadCount > 0)
                <button wire:click="markAllRead"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                    {{ __('Marcar todas lidas') }}
                </button>
            @endif
        </div>

        {{-- Lista --}}
        <div class="overflow-y-auto max-h-[360px] divide-y divide-slate-100 dark:divide-slate-700/60">
            @forelse($notifications as $n)
                @php
                    $data    = $n->data;
                    $isUnread = is_null($n->read_at);
                    $color   = $data['color'] ?? 'slate';
                    $icon    = $data['icon'] ?? 'bell';
                    $colorMap = [
                        'blue'  => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
                        'green' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400',
                        'red'   => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
                        'slate' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
                    ];
                    $iconClass = $colorMap[$color] ?? $colorMap['slate'];
                @endphp
                <div class="flex gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors
                            {{ $isUnread ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">

                    {{-- Ícone --}}
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $iconClass }}">
                        @if($icon === 'calendar')
                            <x-heroicon-o-calendar class="w-4 h-4" />
                        @elseif($icon === 'calendar-days')
                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                        @elseif($icon === 'banknotes')
                            <x-heroicon-o-banknotes class="w-4 h-4" />
                        @else
                            <x-heroicon-o-bell class="w-4 h-4" />
                        @endif
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 leading-tight">
                            {{ $data['title'] ?? '' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-snug truncate">
                            {{ $data['body'] ?? '' }}
                        </p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
                            {{ $n->created_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Ações --}}
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        @if($isUnread)
                            <button wire:click="markRead('{{ $n->id }}')"
                                    class="w-2 h-2 rounded-full bg-primary-500 hover:bg-primary-700 transition-colors mt-1"
                                    title="{{ __('Marcar como lida') }}">
                            </button>
                        @endif
                        <button wire:click="delete('{{ $n->id }}')"
                                class="text-slate-300 hover:text-red-400 dark:text-slate-600 dark:hover:text-red-400 transition-colors mt-auto"
                                title="{{ __('Remover') }}">
                            <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-10 text-center px-4">
                    <x-heroicon-o-bell-slash class="w-8 h-8 text-slate-300 dark:text-slate-600 mb-2" />
                    <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('Nenhuma notificação') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Rodapé --}}
        @if($notifications->isNotEmpty())
            <div class="border-t border-slate-100 dark:border-slate-700 px-4 py-2 text-center">
                <button wire:click="markAllRead"
                        class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    {{ __('Limpar todas') }}
                </button>
            </div>
        @endif
    </div>
</div>
