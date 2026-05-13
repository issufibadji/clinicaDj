<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return ['user' => Auth::user()];
    }
}; ?>

<div class="space-y-6">

    {{-- Boas-vindas --}}
    <div class="flex items-center gap-4">
        @if($user->avatarUrl())
            <img src="{{ $user->avatarUrl() }}" alt="avatar"
                 class="w-10 h-10 rounded-full object-cover flex-shrink-0 border-2 border-primary-200" />
        @else
            <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                {{ $user->initials() }}
            </div>
        @endif
        <div>
            <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                {{ __('Bem-vindo, :name!', ['name' => $user->name]) }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ now()->translatedFormat('l, j \d\e F \d\e Y') }}
            </p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <livewire:dashboard.stats-cards />

    {{-- Linha inferior: gráfico + médico disponível + mini-calendário --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Gráfico mensal (ocupa 2/3) --}}
        <div class="lg:col-span-2">
            <livewire:dashboard.appointment-chart />
        </div>

        {{-- Médico de plantão (ocupa 1/3) --}}
        <div>
            <livewire:dashboard.doctor-on-duty />
        </div>

    </div>

    {{-- Mini-calendário (linha própria) --}}
    <livewire:dashboard.mini-calendar />

</div>
