<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Clínica DR.João Mendes') }}</title>

    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Fundo escuro externo, cartão centralizado como MeFy --}}
<body class="font-sans antialiased bg-[#060d1f] min-h-screen flex items-center justify-center p-4">

    {{-- ╔══════════════════════════════════════════════════════════╗
         ║  CARTÃO PRINCIPAL (max 960px, cantos arredondados)       ║
         ╚══════════════════════════════════════════════════════════╝ --}}
    <div class="w-full max-w-[960px] relative rounded-2xl overflow-hidden shadow-[0_30px_80px_rgba(0,0,0,0.6)] flex"
         style="min-height: 580px;">

        {{-- ═══ ESQUERDA: Painel do formulário (claro / lavanda) ═══ --}}
        <div class="login-left w-full lg:w-[43%] flex flex-col relative"
             style="background: linear-gradient(155deg, #f9f8ff 0%, #ede8fd 55%, #e4dcfb 100%);">

            {{-- Logo topo --}}
            <div class="login-logo px-10 pt-9 flex items-center gap-2.5 flex-shrink-0">
                <div class="w-9 h-9 rounded-xl bg-primary-500 flex items-center justify-center flex-shrink-0 shadow-md shadow-primary-500/30">
                    <x-heroicon-o-heart class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="font-bold text-slate-700 text-sm tracking-tight leading-tight">Clínica DR.João Mendes</p>
                    <p class="text-[10px] text-slate-400 tracking-widest uppercase">Sistema de Gestão</p>
                </div>
            </div>

            {{-- Conteúdo do formulário ($slot) --}}
            <div class="flex-1 flex items-center justify-center px-10 py-6">
                <div class="w-full">
                    {{ $slot }}
                </div>
            </div>

            {{-- Rodapé mobile --}}
            <p class="lg:hidden text-center text-[10px] text-slate-400 pb-5">
                © {{ date('Y') }} Clínica DR.João Mendes · Todos os direitos reservados
            </p>

            {{-- Círculo decorativo no canto inferior esquerdo do painel claro --}}
            <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full opacity-20 pointer-events-none"
                 style="background: radial-gradient(circle, #c4b5fd, transparent 70%);"></div>

        </div>

        {{-- ═══ DIREITA: Painel decorativo escuro ═══ --}}
        <div class="login-right hidden lg:flex flex-1 flex-col justify-end relative overflow-hidden"
             style="background: linear-gradient(150deg, #0c1b42 0%, #0e2060 100%);">

            {{-- ── Divisor orgânico: wave pintada na borda esquerda do painel escuro ── --}}
            <svg class="absolute inset-y-0 left-0 z-30 pointer-events-none"
                 style="width: 38px;"
                 viewBox="0 0 38 700" preserveAspectRatio="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M0,0 L18,0
                         C26,85  34,175  12,295
                         C-2,390  24,495  10,610
                         L10,700 L0,700 Z"
                      fill="#e4dcfb"/>
            </svg>

            {{-- ── Grade de ícones médicos (fundo, grid regular) ── --}}
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                @php $icons = [
                    'heart','beaker','shield-check','calendar-days','chart-bar','bell',
                    'user-group','document-text','banknotes','lock-closed','star','clock',
                    'chart-pie','magnifying-glass','user-circle','phone','envelope','globe-alt',
                    'cog-6-tooth','credit-card','building-office','printer','eye','cpu-chip',
                    'finger-print','fire','sparkles','trophy','bolt','check-circle',
                ]; @endphp
                @foreach($icons as $i => $icon)
                    @php
                        $col = $i % 6;
                        $row = (int)($i / 6);
                        $left = 8 + $col * 15;
                        $top  = 6  + $row * 20;
                        $op   = ($i % 2 === 0) ? '0.07' : '0.05';
                    @endphp
                    <div class="absolute text-white" style="left:{{ $left }}%; top:{{ $top }}%; opacity:{{ $op }};">
                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5"/>
                    </div>
                @endforeach
            </div>

            {{-- ── Folhas SVG — canto superior direito ── --}}
            <svg class="absolute -top-6 -right-6 w-52 h-52 pointer-events-none opacity-[0.22]"
                 viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg">
                {{-- Folha grande --}}
                <path d="M160,15 C185,35 205,75 195,120 C185,165 155,188 118,180
                         C82,172 60,148 68,115 C76,82 100,45 135,22 C147,14 155,10 160,15Z"
                      fill="#1e3f8c"/>
                <line x1="160" y1="15" x2="118" y2="180" stroke="#1e3f8c" stroke-width="3.5" stroke-linecap="round"/>
                {{-- Folha secundária --}}
                <path d="M200,60 C215,90 218,135 205,165 C198,145 190,115 195,92Z"
                      fill="#172e6a" opacity="0.8"/>
                {{-- Caule --}}
                <path d="M118,180 C115,195 112,210 108,220" stroke="#1e3f8c" stroke-width="3" stroke-linecap="round"/>
            </svg>

            {{-- ── Folhas SVG — canto inferior esquerdo ── --}}
            <svg class="absolute -bottom-8 -left-6 w-60 h-60 pointer-events-none opacity-[0.22]"
                 viewBox="0 0 240 240" style="transform: rotate(20deg);" xmlns="http://www.w3.org/2000/svg">
                <path d="M70,215 C48,195 25,155 35,112 C45,70 78,48 112,53
                         C146,58 168,82 162,115 C156,148 133,182 100,202 C86,212 74,220 70,215Z"
                      fill="#1a3580"/>
                <line x1="70" y1="215" x2="118" y2="58" stroke="#1a3580" stroke-width="3.5" stroke-linecap="round"/>
                <path d="M22,112 C10,80 10,40 22,12 C28,35 38,68 34,90Z"
                      fill="#13275e" opacity="0.8"/>
                <path d="M70,215 C65,228 60,238 55,245" stroke="#1a3580" stroke-width="3" stroke-linecap="round"/>
            </svg>

            {{-- ── Estetoscópio SVG grande ── --}}
            <svg class="absolute right-8 top-1/2 -translate-y-1/2 w-52 h-52 text-white pointer-events-none opacity-[0.28]"
                 viewBox="0 0 130 155" fill="none" stroke="currentColor"
                 stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"
                 xmlns="http://www.w3.org/2000/svg">
                {{-- Braços do Y --}}
                <path d="M30,10 Q30,42 54,52"/>
                <path d="M58,10 Q58,42 54,52"/>
                {{-- Tubo principal --}}
                <path d="M54,52 Q55,88 82,96 Q102,102 102,120"/>
                {{-- Membrana (disco grande) --}}
                <circle cx="102" cy="135" r="16" stroke-width="4"/>
                <circle cx="102" cy="135" r="7"  stroke-width="2" opacity="0.4"/>
                {{-- Pontas auriculares --}}
                <circle cx="30"  cy="8"  r="6"  fill="currentColor" stroke="none"/>
                <circle cx="58"  cy="8"  r="6"  fill="currentColor" stroke="none"/>
            </svg>

            {{-- ── Coração + globo (elemento 3D central) ── --}}
            <div class="absolute left-[28%] top-[42%] -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-28 h-28 opacity-50" viewBox="0 0 110 110" xmlns="http://www.w3.org/2000/svg">
                    {{-- Coração --}}
                    <path d="M55,92 C30,75 8,56 8,32 C8,18 20,8 34,8 C42,8 50,13 55,21
                             C60,13 68,8 76,8 C90,8 102,18 102,32 C102,56 80,75 55,92Z"
                          fill="#3b5bdb" opacity="0.85"/>
                    {{-- Globo (círculo + meridianos) --}}
                    <circle cx="55" cy="48" r="28" fill="none" stroke="#93c5fd" stroke-width="1.5" opacity="0.6"/>
                    <ellipse cx="55" cy="48" rx="13" ry="28" fill="none" stroke="#93c5fd" stroke-width="1.5" opacity="0.5"/>
                    <line x1="27" y1="48" x2="83" y2="48" stroke="#93c5fd" stroke-width="1.5" opacity="0.5"/>
                    {{-- Nós de rede --}}
                    <circle cx="55" cy="24" r="3"  fill="#bfdbfe" opacity="0.9"/>
                    <circle cx="76" cy="40" r="2.5" fill="#bfdbfe" opacity="0.8"/>
                    <circle cx="38" cy="62" r="2.5" fill="#bfdbfe" opacity="0.8"/>
                    <circle cx="70" cy="65" r="2"  fill="#bfdbfe" opacity="0.7"/>
                    {{-- Linhas de rede --}}
                    <line x1="55" y1="24" x2="76" y2="40" stroke="#bfdbfe" stroke-width="1" opacity="0.55"/>
                    <line x1="76" y1="40" x2="38" y2="62" stroke="#bfdbfe" stroke-width="1" opacity="0.55"/>
                    <line x1="38" y1="62" x2="70" y2="65" stroke="#bfdbfe" stroke-width="1" opacity="0.55"/>
                </svg>
            </div>

            {{-- ── Conteúdo inferior: tagline + badges + copyright ── --}}
            <div class="relative z-10 px-12 pb-10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-6 h-0.5 bg-green-500 rounded-full"></div>
                    <span class="text-green-500 text-[10px] font-medium tracking-[0.2em] uppercase">Sistema de Gestão</span>
                </div>

                <h2 class="login-title text-2xl font-bold text-white leading-tight mb-2">
                    Gestão clínica<br>inteligente.
                </h2>

                <p class="text-slate-500 text-xs mb-5 max-w-[260px] leading-relaxed">
                    Agendamentos, pacientes, finanças e equipe médica em um painel unificado.
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap gap-2 mb-8">
                    @foreach([
                        ['icon' => 'calendar-days', 'label' => 'Agendamentos'],
                        ['icon' => 'users',          'label' => 'Pacientes'],
                        ['icon' => 'banknotes',      'label' => 'Financeiro'],
                        ['icon' => 'lock-closed',    'label' => '2FA'],
                        ['icon' => 'shield-check',   'label' => 'Auditoria'],
                    ] as $tag)
                        <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full
                                     border border-white/10 bg-white/[0.04]
                                     text-white/40 text-[10px] font-medium tracking-wide
                                     hover:border-green-500/40 hover:text-green-400 hover:bg-green-500/10
                                     transition-all duration-300 cursor-default">
                            <x-dynamic-component :component="'heroicon-o-' . $tag['icon']" class="w-3 h-3"/>
                            {{ $tag['label'] }}
                        </span>
                    @endforeach
                </div>

                <p class="text-slate-600 text-[10px]">
                    © {{ date('Y') }} Clínica DR.João Mendes · Todos os direitos reservados
                </p>
            </div>

        </div>{{-- /right panel --}}

    </div>{{-- /main card --}}

</body>
</html>
