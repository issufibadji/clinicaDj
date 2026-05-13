<header class="flex items-center justify-between h-16 px-4 sm:px-6
              bg-white dark:bg-slate-800
              border-b border-slate-200 dark:border-slate-700
              flex-shrink-0 z-10">

    {{-- Esquerda: toggle sidebar + título da página --}}
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen"
                class="icon-btn text-slate-500 dark:text-slate-400"
                title="{{ __('Expandir / recolher menu') }}">
            <x-heroicon-o-bars-3 class="w-5 h-5" />
        </button>

        @isset($header)
            <h1 class="hidden sm:block text-sm font-semibold text-slate-700 dark:text-slate-200">
                {{ $header }}
            </h1>
        @endisset
    </div>

    {{-- Direita: idioma + dark mode + notificações + dropdown usuário --}}
    <div class="flex items-center gap-1">

        {{-- Seletor de idioma --}}
        <div x-data="{ langOpen: false }" class="relative">
            <button @click="langOpen = !langOpen"
                    @click.outside="langOpen = false"
                    class="icon-btn flex items-center gap-1.5 px-2 text-slate-500 dark:text-slate-400"
                    title="{{ __('Idioma') }}">
                <x-heroicon-o-globe-alt class="w-5 h-5" />
                <span class="hidden sm:inline text-xs font-semibold uppercase">{{ app()->getLocale() }}</span>
            </button>

            <div x-show="langOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute right-0 top-full mt-2 w-40
                        bg-white dark:bg-slate-800 rounded-xl shadow-lg
                        border border-slate-200 dark:border-slate-700 py-1 z-50"
                 style="display: none;">
                @foreach(['pt' => ['flag' => '🇧🇷', 'label' => 'Português'], 'en' => ['flag' => '🇺🇸', 'label' => 'English'], 'fr' => ['flag' => '🇫🇷', 'label' => 'Français']] as $code => $lang)
                    <a href="{{ route('lang.switch', $code) }}"
                       class="flex items-center gap-2.5 px-3 py-2 text-sm transition-colors
                              {{ app()->getLocale() === $code
                                  ? 'text-primary-600 dark:text-primary-400 font-semibold bg-primary-50 dark:bg-primary-900/20'
                                  : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60' }}">
                        <span class="text-base leading-none">{{ $lang['flag'] }}</span>
                        <span>{{ $lang['label'] }}</span>
                        @if(app()->getLocale() === $code)
                            <x-heroicon-o-check class="w-3.5 h-3.5 ml-auto text-primary-600 dark:text-primary-400" />
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Toggle dark mode --}}
        <button
            @click="
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme',
                    document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                );
            "
            class="icon-btn text-slate-500 dark:text-slate-400"
            title="{{ __('Alternar tema') }}">
            <x-heroicon-o-sun class="w-5 h-5 dark:hidden" />
            <x-heroicon-o-moon class="w-5 h-5 hidden dark:block" />
        </button>

        {{-- Notificações --}}
        <livewire:notifications.notification-panel />

        {{-- Separador --}}
        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-1"></div>

        {{-- Dropdown do usuário (Livewire) --}}
        <livewire:layout.navigation />

    </div>
</header>
