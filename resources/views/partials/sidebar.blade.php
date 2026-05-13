@php
    use App\Actions\Admin\System\GetSidebarMenus;
    $menuGroups = GetSidebarMenus::forUser();
@endphp

{{-- Overlay para mobile --}}
<div x-show="sidebarOpen && window.innerWidth < 1024"
     @click="sidebarOpen = false"
     class="fixed inset-0 z-20 bg-black/50 lg:hidden"
     style="display: none;"></div>

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:-translate-x-full'"
    class="fixed lg:static inset-y-0 left-0 z-30 flex flex-col w-64 bg-sidebar flex-shrink-0
           transform transition-transform duration-200 ease-in-out lg:translate-x-0"
    x-show="sidebarOpen || window.innerWidth >= 1024"
    style="display: flex;">

    {{-- Logo --}}
    <div class="flex items-center h-16 px-5 border-b border-slate-700 flex-shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-heart class="w-4 h-4 text-white" />
            </div>
            <span class="text-white font-bold text-[15px] leading-tight tracking-tight">Clínica DR.João Mendes</span>
        </div>
    </div>

    {{-- Navegação --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-1">
        @foreach($menuGroups as $group => $items)
            <div x-data="{ open: true }" class="mb-1">

                {{-- Cabeçalho do grupo --}}
                <button @click="open = !open"
                        class="flex items-center justify-between w-full px-3 py-1.5 mb-0.5
                               text-[10px] font-bold uppercase tracking-widest
                               text-slate-400 hover:text-slate-300 transition-colors rounded">
                    <span>{{ __($group) }}</span>
                    <span :class="{ 'rotate-180': !open }" class="transition-transform duration-200">
                        <x-heroicon-o-chevron-down class="w-3 h-3" />
                    </span>
                </button>

                {{-- Itens do grupo --}}
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="space-y-0.5">

                    @foreach($items as $item)
                        @php
                            $hasRoute = \Illuminate\Support\Facades\Route::has($item->route);
                            $isActive = $hasRoute && request()->routeIs($item->route . '*');
                            $url      = $hasRoute ? route($item->route) : '#';
                        @endphp

                        <a href="{{ $url }}"
                           @if($hasRoute) wire:navigate @endif
                           @if(!$hasRoute) title="Em breve" @endif
                           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium
                                  transition-colors duration-150
                                  {{ $isActive
                                      ? 'bg-primary-600 text-white shadow-sm'
                                      : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}
                                  {{ !$hasRoute ? 'opacity-50 cursor-default' : '' }}">

                            <x-dynamic-component
                                :component="$item->icon"
                                class="w-[18px] h-[18px] flex-shrink-0
                                       {{ $isActive ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" />

                            <span class="flex-1 truncate">{{ __($item->label) }}</span>

                            @if(!$hasRoute)
                                <span class="text-[10px] text-slate-500 font-normal">{{ __('em breve') }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Usuário logado --}}
    <div class="flex-shrink-0 p-3 border-t border-slate-700">
        <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-slate-700/50 transition-colors">
            @if(auth()->user()->avatarUrl())
                <img src="{{ auth()->user()->avatarUrl() }}"
                     alt="{{ auth()->user()->name }}"
                     class="w-8 h-8 rounded-full object-cover flex-shrink-0 ring-2 ring-slate-600">
            @else
                <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center
                            text-white text-xs font-bold flex-shrink-0 ring-2 ring-primary-700">
                    {{ auth()->user()->initials() }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate leading-tight">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-[11px] text-slate-400 truncate capitalize leading-tight mt-0.5">
                    {{ auth()->user()->getRoleNames()->first() ?? 'Sem papel' }}
                </p>
            </div>
        </div>
    </div>

</aside>
