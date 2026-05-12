<header class="flex items-center justify-between h-16 px-4 sm:px-6
              bg-white dark:bg-slate-800
              border-b border-slate-200 dark:border-slate-700
              flex-shrink-0 z-10">

    {{-- Esquerda: toggle sidebar + título da página --}}
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen"
                class="icon-btn text-slate-500 dark:text-slate-400"
                title="Expandir / recolher menu">
            <x-heroicon-o-bars-3 class="w-5 h-5" />
        </button>

        @isset($header)
            <h1 class="hidden sm:block text-sm font-semibold text-slate-700 dark:text-slate-200">
                {{ $header }}
            </h1>
        @endisset
    </div>

    {{-- Direita: dark mode toggle + dropdown usuário --}}
    <div class="flex items-center gap-1">

        {{-- Toggle dark mode --}}
        <button
            @click="
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme',
                    document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                );
            "
            class="icon-btn text-slate-500 dark:text-slate-400"
            title="Alternar tema">
            <x-heroicon-o-sun class="w-5 h-5 dark:hidden" />
            <x-heroicon-o-moon class="w-5 h-5 hidden dark:block" />
        </button>

        {{-- Separador --}}
        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-1"></div>

        {{-- Dropdown do usuário (Livewire) --}}
        <livewire:layout.navigation />

    </div>
</header>
