<?php

use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function toggleVisible(string $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_visible' => ! $item->is_visible]);
        $this->invalidateMenuCache();
        $this->dispatch('flash', type: 'success', message: "Menu \"{$item->label}\" " . ($item->is_visible ? 'exibido' : 'ocultado') . '.');
    }

    public function updateLevel(string $id, int $level): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['min_level' => $level]);
        $this->invalidateMenuCache();
        $this->dispatch('flash', type: 'success', message: "Nível de acesso de \"{$item->label}\" atualizado.");
    }

    private function invalidateMenuCache(): void
    {
        foreach (range(1, 4) as $level) {
            Cache::forget("sidebar.menu.level.{$level}");
        }
    }

    public function with(): array
    {
        return [
            'groups' => MenuItem::ordered()->get()->groupBy('group'),
        ];
    }
}; ?>

<div>
    {{-- Cabeçalho da página --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Gerenciar Menus</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Controle a visibilidade e o nível de acesso de cada item do menu lateral.
            </p>
        </div>
        <span class="text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg">
            Alterações aplicadas em tempo real
        </span>
    </div>

    {{-- Flash em linha (evento Livewire) --}}
    <div
        x-data="{ show: false, type: '', message: '' }"
        x-on:flash.window="
            type = $event.detail.type;
            message = $event.detail.message;
            show = true;
            setTimeout(() => show = false, 3000)
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-end="opacity-0"
        class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm font-medium
               bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400
               border border-emerald-200 dark:border-emerald-700"
        style="display:none">
        <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />
        <span x-text="message"></span>
    </div>

    {{-- Grupos de menus --}}
    <div class="space-y-5">
        @foreach($groups as $group => $items)
            <div class="card overflow-hidden p-0">
                {{-- Cabeçalho do grupo --}}
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        {{ $group }}
                    </h2>
                </div>

                {{-- Tabela --}}
                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($items as $item)
                        <div class="flex items-center gap-4 px-5 py-3.5
                                    {{ $item->is_visible ? '' : 'opacity-50' }}
                                    transition-opacity duration-200">

                            {{-- Ícone --}}
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                        {{ $item->is_visible
                                            ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                                <x-dynamic-component :component="$item->icon" class="w-4 h-4" />
                            </div>

                            {{-- Label + rota --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                    {{ $item->label }}
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 font-mono truncate">
                                    {{ $item->route }}
                                </p>
                            </div>

                            {{-- Dropdown min_level --}}
                            <div class="flex-shrink-0">
                                <select
                                    wire:change="updateLevel('{{ $item->id }}', $event.target.value)"
                                    class="text-xs rounded-lg border border-slate-200 dark:border-slate-700
                                           bg-white dark:bg-slate-800
                                           text-slate-700 dark:text-slate-300
                                           px-2 py-1.5 pr-7 focus:ring-1 focus:ring-primary-500
                                           focus:border-primary-500 cursor-pointer">
                                    <option value="1" {{ $item->min_level == 1 ? 'selected' : '' }}>Somente admin</option>
                                    <option value="2" {{ $item->min_level == 2 ? 'selected' : '' }}>Admin + Médico</option>
                                    <option value="3" {{ $item->min_level == 3 ? 'selected' : '' }}>+ Recepcionista</option>
                                    <option value="4" {{ $item->min_level == 4 ? 'selected' : '' }}>Todos</option>
                                </select>
                            </div>

                            {{-- Toggle visibilidade --}}
                            <div class="flex-shrink-0">
                                <button
                                    wire:click="toggleVisible('{{ $item->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleVisible('{{ $item->id }}')"
                                    title="{{ $item->is_visible ? 'Ocultar item' : 'Exibir item' }}"
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                                           {{ $item->is_visible
                                                ? 'bg-primary-600'
                                                : 'bg-slate-300 dark:bg-slate-600' }}">
                                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-200
                                                 {{ $item->is_visible ? 'translate-x-[18px]' : 'translate-x-[3px]' }}">
                                    </span>
                                </button>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
