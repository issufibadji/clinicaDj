<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $filter = 'all'; // all | unread | read

    public function updatedFilter(): void { $this->resetPage(); }

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

    public function deleteAll(): void
    {
        auth()->user()->notifications()->delete();
        $this->resetPage();
    }

    public function with(): array
    {
        $query = auth()->user()->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(20);
        $unreadCount   = auth()->user()->unreadNotifications()->count();
        $totalCount    = auth()->user()->notifications()->count();

        return compact('notifications', 'unreadCount', 'totalCount');
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('Notificações') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                {{ $unreadCount > 0
                    ? trans_choice(':count não lida|:count não lidas', $unreadCount, ['count' => $unreadCount])
                    : __('Todas lidas') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
                <button wire:click="markAllRead"
                        class="btn-secondary flex items-center gap-2 px-3 py-2 text-sm">
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                    {{ __('Marcar todas lidas') }}
                </button>
            @endif
            @if($totalCount > 0)
                <button wire:click="deleteAll"
                        wire:confirm="{{ __('Remover todas as notificações?') }}"
                        class="flex items-center gap-2 px-3 py-2 text-sm rounded-xl border
                               border-red-200 dark:border-red-700 text-red-600 dark:text-red-400
                               hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    {{ __('Limpar tudo') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex gap-1 mb-5 bg-slate-100 dark:bg-slate-800 rounded-xl p-1 w-fit">
        @foreach(['all' => __('Todas'), 'unread' => __('Não lidas'), 'read' => __('Lidas')] as $val => $label)
            <button wire:click="$set('filter', '{{ $val }}')"
                    class="px-4 py-1.5 text-sm font-medium rounded-lg transition-colors
                           {{ $filter === $val
                               ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 shadow-sm'
                               : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                {{ $label }}
                @if($val === 'unread' && $unreadCount > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold
                                 bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
                        {{ $unreadCount }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Lista --}}
    @if($notifications->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <x-heroicon-o-bell-slash class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" />
            <p class="text-base font-medium text-slate-500 dark:text-slate-400">{{ __('Nenhuma notificação') }}</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">{{ __('Você está em dia!') }}</p>
        </div>
    @else
        <div class="card divide-y divide-slate-100 dark:divide-slate-700/60 p-0 overflow-hidden">
            @foreach($notifications as $n)
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

                <div class="flex gap-4 px-5 py-4 transition-colors
                            {{ $isUnread ? 'bg-primary-50/50 dark:bg-primary-900/10' : 'hover:bg-slate-50 dark:hover:bg-slate-700/20' }}">

                    {{-- Ícone --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center {{ $iconClass }}">
                        @if($icon === 'calendar')
                            <x-heroicon-o-calendar class="w-5 h-5" />
                        @elseif($icon === 'calendar-days')
                            <x-heroicon-o-calendar-days class="w-5 h-5" />
                        @elseif($icon === 'banknotes')
                            <x-heroicon-o-banknotes class="w-5 h-5" />
                        @else
                            <x-heroicon-o-bell class="w-5 h-5" />
                        @endif
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $data['title'] ?? '' }}
                                    @if($isUnread)
                                        <span class="ml-2 inline-block w-2 h-2 rounded-full bg-primary-500 align-middle"></span>
                                    @endif
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $data['body'] ?? '' }}
                                </p>
                            </div>
                            <span class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0 mt-0.5">
                                {{ $n->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Ações --}}
                        <div class="flex items-center gap-3 mt-2">
                            @if(!empty($data['url']))
                                <a href="{{ $data['url'] }}" wire:navigate
                                   wire:click="markRead('{{ $n->id }}')"
                                   class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                                    {{ __('Ver detalhes') }} →
                                </a>
                            @endif
                            @if($isUnread)
                                <button wire:click="markRead('{{ $n->id }}')"
                                        class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                    {{ __('Marcar como lida') }}
                                </button>
                            @endif
                            <button wire:click="delete('{{ $n->id }}')"
                                    class="text-xs text-slate-300 hover:text-red-500 dark:text-slate-600 dark:hover:text-red-400 transition-colors ml-auto">
                                {{ __('Remover') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
