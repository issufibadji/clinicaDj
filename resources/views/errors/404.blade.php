<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Página não encontrada') }} — {{ config('app.name') }}</title>
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 font-sans antialiased">

    <div class="min-h-screen flex flex-col items-center justify-center text-center px-6">

        <div class="w-24 h-24 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center mb-6">
            <x-heroicon-o-magnifying-glass class="w-12 h-12 text-blue-400" />
        </div>

        <p class="text-7xl font-extrabold text-blue-200 dark:text-blue-900/60 mb-2 select-none">404</p>

        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-2">
            {{ __('Página não encontrada') }}
        </h1>

        <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-sm">
            {{ __('A página que você está procurando não existe ou foi removida.') }}
        </p>

        <div class="flex gap-3 flex-wrap justify-center">
            <a href="javascript:history.back()"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl
                      bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300
                      hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                {{ __('Voltar') }}
            </a>
            @auth
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl
                          bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                    <x-heroicon-o-home class="w-4 h-4" />
                    {{ __('Dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl
                          bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                    {{ __('Fazer login') }}
                </a>
            @endauth
        </div>

    </div>

</body>
</html>
