<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(config('services.webpush.vapid_public_key'))
        <meta name="vapid-public-key" content="{{ config('services.webpush.vapid_public_key') }}">
    @endif
    <title>{{ config('app.name', 'Clínica DR.João Mendes') }}</title>

    {{-- Dark mode: aplica antes do render para evitar flash --}}
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans antialiased h-full">

    <div class="flex h-screen overflow-hidden"
         x-data="{ sidebarOpen: window.innerWidth >= 1024, sidebarCollapsed: false }">

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Área principal --}}
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            {{-- Topbar --}}
            @include('partials.topbar')

            {{-- Conteúdo da página --}}
            <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900">
                <div class="p-6">
                    @include('partials.flash')
                    {{ $slot }}
                </div>
            </main>

        </div>
    </div>

</body>
</html>
