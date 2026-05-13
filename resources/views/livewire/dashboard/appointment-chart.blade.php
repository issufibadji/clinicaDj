<?php

use App\Models\Appointment;
use Livewire\Volt\Component;

new class extends Component
{
    public function with(): array
    {
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->startOfMonth());

        $raw = Appointment::where('scheduled_at', '>=', $months->first())
            ->get(['scheduled_at'])
            ->groupBy(fn($a) => $a->scheduled_at->format('Y-m'))
            ->map->count();

        $labels = $months->map(fn($m) => rtrim($m->isoFormat('MMM'), '.') . '/' . $m->format('y'))->values()->all();
        $values = $months->map(fn($m) => (int) ($raw->get($m->format('Y-m'), 0)))->values()->all();

        return compact('labels', 'values');
    }
}; ?>

<div class="card">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
        <x-heroicon-o-chart-bar class="w-4 h-4 text-primary-600 dark:text-primary-400" />
        {{ __('Consultas por Mês') }}
    </h2>

    <div wire:ignore
         x-data="appointmentChart(@js($labels), @js($values))">
        <div class="h-48">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>
</div>
