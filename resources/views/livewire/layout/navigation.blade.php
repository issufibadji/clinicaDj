<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div x-data="{ open: false }"
     @keydown.escape.window="open = false"
     @click.outside="open = false"
     class="relative">

    {{-- Botão trigger --}}
    <button @click="open = !open"
            class="flex items-center gap-2 rounded-xl px-2.5 py-1.5
                   text-sm font-medium text-slate-700 dark:text-slate-200
                   hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">

        @if(auth()->user()->avatarUrl())
            <img src="{{ auth()->user()->avatarUrl() }}"
                 class="w-7 h-7 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-600"
                 alt="">
        @else
            <div class="w-7 h-7 rounded-full bg-primary-600 flex items-center justify-center
                        text-white text-xs font-bold ring-2 ring-primary-100 dark:ring-primary-900">
                {{ auth()->user()->initials() }}
            </div>
        @endif

        <span class="hidden md:block max-w-[140px] truncate">{{ auth()->user()->name }}</span>

        <span :class="{ 'rotate-180': open }" class="transition-transform duration-200">
            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-slate-400" />
        </span>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 top-full mt-2 w-56
                bg-white dark:bg-slate-800 rounded-xl shadow-lg
                border border-slate-200 dark:border-slate-700 py-1 z-50"
         style="display: none;">

        {{-- Info do usuário --}}
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
                {{ auth()->user()->name }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                {{ auth()->user()->email }}
            </p>
            @php $role = auth()->user()->getRoleNames()->first(); @endphp
            @if($role)
                <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium
                             bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 capitalize">
                    {{ $role }}
                </span>
            @endif
        </div>

        {{-- Links --}}
        <div class="py-1">
            <a href="{{ route('profile') }}" wire:navigate @click="open = false"
               class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300
                      hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">
                <x-heroicon-o-user-circle class="w-4 h-4 text-slate-400" />
                Meu Perfil
            </a>
        </div>

        {{-- Logout --}}
        <div class="border-t border-slate-100 dark:border-slate-700 py-1">
            <button wire:click="logout"
                    class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400
                           hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                Sair
            </button>
        </div>
    </div>
</div>
