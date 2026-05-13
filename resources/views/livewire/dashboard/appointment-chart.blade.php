<?php

use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;

new class extends Component
{
    private static array $MONTH_ABBR = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
        5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
    ];

    public function with(): array
    {
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->startOfMonth());

        $raw = Appointment::selectRaw("DATE_FORMAT(scheduled_at, '%Y-%m') as ym, COUNT(*) as total")
            ->where('scheduled_at', '>=', $months->first())
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $abbr = self::$MONTH_ABBR;

        $labels = $months->map(fn($m) => $abbr[$m->month] . '/' . $m->format('y'))->values()->all();
        $values = $months->map(fn($m) => (int) ($raw->get($m->format('Y-m'), 0)))->values()->all();

        return compact('labels', 'values');
    }
}; ?>

<div class="card">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
        <x-heroicon-o-chart-bar class="w-4 h-4 text-indigo-500" />
        Consultas por Mês
    </h2>

    <div wire:ignore
         x-data="appointmentChart(@js($labels), @js($values))">
        <div class="h-48">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>
</div>
