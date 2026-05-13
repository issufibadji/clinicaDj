<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use Livewire\Volt\Component;

new class extends Component
{
    public function with(): array
    {
        $today = today();

        return [
            'appointmentsToday' => Appointment::whereDate('scheduled_at', $today)->count(),
            'patientsThisMonth' => Patient::whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->count(),
            'availableDoctors'  => Doctor::where('is_available', true)->count(),
            'revenueToday'      => Payment::where('status', 'paid')
                ->whereDate('created_at', $today)
                ->sum('amount'),
        ];
    }
}; ?>

<div wire:poll.30s class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

    <div class="card flex items-center gap-4">
        <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex-shrink-0">
            <x-heroicon-o-calendar-days class="w-6 h-6 text-primary-600 dark:text-primary-400" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Consultas Hoje</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $appointmentsToday }}</p>
        </div>
    </div>

    <div class="card flex items-center gap-4">
        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex-shrink-0">
            <x-heroicon-o-users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Novos Pacientes (mês)</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $patientsThisMonth }}</p>
        </div>
    </div>

    <div class="card flex items-center gap-4">
        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex-shrink-0">
            <x-heroicon-o-user-circle class="w-6 h-6 text-amber-600 dark:text-amber-400" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Médicos Disponíveis</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $availableDoctors }}</p>
        </div>
    </div>

    <div class="card flex items-center gap-4">
        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex-shrink-0">
            <x-heroicon-o-banknotes class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Receita do Dia</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                R$ {{ number_format($revenueToday, 2, ',', '.') }}
            </p>
        </div>
    </div>

</div>
