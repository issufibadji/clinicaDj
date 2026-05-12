<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- KPI Cards (placeholder até a Fase 13) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        @foreach([
            ['label' => 'Consultas Hoje',     'value' => '0',    'icon' => 'heroicon-o-calendar-days',  'color' => 'text-primary-600 dark:text-primary-400',  'bg' => 'bg-primary-50 dark:bg-primary-900/20'],
            ['label' => 'Novos Pacientes',    'value' => '0',    'icon' => 'heroicon-o-users',           'color' => 'text-blue-600 dark:text-blue-400',         'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
            ['label' => 'Médicos Disponíveis','value' => '0',    'icon' => 'heroicon-o-user-circle',     'color' => 'text-amber-600 dark:text-amber-400',       'bg' => 'bg-amber-50 dark:bg-amber-900/20'],
            ['label' => 'Receita do Dia',     'value' => 'R$ 0', 'icon' => 'heroicon-o-banknotes',      'color' => 'text-emerald-600 dark:text-emerald-400',   'bg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
        ] as $kpi)
            <div class="card flex items-center gap-4">
                <div class="p-3 rounded-xl {{ $kpi['bg'] }} flex-shrink-0">
                    <x-dynamic-component
                        :component="$kpi['icon']"
                        class="w-6 h-6 {{ $kpi['color'] }}" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 truncate">
                        {{ $kpi['label'] }}
                    </p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                        {{ $kpi['value'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Boas-vindas --}}
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-600 flex items-center justify-center
                        text-white text-lg font-bold flex-shrink-0">
                {{ auth()->user()->initials() }}
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                    Bem-vindo, {{ auth()->user()->name }}!
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Você está logado como
                    <x-badge color="green" class="ml-1 capitalize">
                        {{ auth()->user()->getRoleNames()->first() ?? 'sem papel' }}
                    </x-badge>
                </p>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
            <x-alert type="info" :dismissible="false">
                Dashboard completo será implementado na <strong>Fase 13</strong> —
                KPIs em tempo real, gráfico mensal, médico de plantão e mini-calendário.
            </x-alert>
        </div>
    </div>

</x-app-layout>
