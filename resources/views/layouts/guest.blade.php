<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Clínica JM') }}</title>

    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-900">

    <div class="min-h-screen flex">

        {{-- Painel decorativo esquerdo (apenas desktop) --}}
        <div class="hidden lg:flex lg:w-2/5 xl:w-1/2 bg-sidebar flex-col justify-between p-12 relative overflow-hidden">

            {{-- Decoração de fundo --}}
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-0 right-0 w-96 h-96 bg-primary-400 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-primary-400 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            </div>

            {{-- Logo --}}
            <div class="relative flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-heart class="w-5 h-5 text-white" />
                </div>
                <span class="text-white font-bold text-xl tracking-tight">Clínica JM</span>
            </div>

            {{-- Tagline central --}}
            <div class="relative">
                <h1 class="text-3xl font-bold text-white leading-snug">
                    Gestão clínica<br>inteligente.
                </h1>
                <p class="text-slate-400 mt-4 text-sm leading-relaxed max-w-xs">
                    Agendamentos, pacientes, finanças e equipe médica — tudo em um painel unificado e seguro.
                </p>

                {{-- Badges de recursos --}}
                <div class="flex flex-wrap gap-2 mt-6">
                    @foreach(['Agendamentos', 'Pacientes', 'Financeiro', '2FA', 'Auditoria'] as $tag)
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Rodapé --}}
            <p class="relative text-slate-500 text-xs">
                © {{ date('Y') }} Clínica JM · Todos os direitos reservados
            </p>
        </div>

        {{-- Painel direito: formulário --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-10 bg-slate-50 dark:bg-slate-900">
            <div class="w-full max-w-md">

                {{-- Logo mobile --}}
                <div class="lg:hidden flex items-center justify-center gap-2.5 mb-8">
                    <div class="w-9 h-9 rounded-xl bg-primary-500 flex items-center justify-center">
                        <x-heroicon-o-heart class="w-5 h-5 text-white" />
                    </div>
                    <span class="font-bold text-lg text-slate-800 dark:text-slate-100 tracking-tight">
                        Clínica JM
                    </span>
                </div>

                {{-- Conteúdo (slot) --}}
                {{ $slot }}

            </div>
        </div>

    </div>

</body>
</html>
