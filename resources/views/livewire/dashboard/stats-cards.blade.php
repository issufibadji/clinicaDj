<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public function with(): array
    {
        $user  = Auth::user();
        $today = today();

        // Shared — admin + recepcionista
        $appointmentsToday   = Appointment::whereDate('scheduled_at', $today)->count();
        $patientsThisMonth   = Patient::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)->count();
        $availableDoctors    = Doctor::where('is_available', true)->count();
        $pendingAppointments = Appointment::where('status', 'scheduled')->count();

        // Admin + financeiro
        $revenueToday    = Payment::where('status', 'paid')
            ->whereDate('created_at', $today)->sum('amount');
        $revenueMonth    = Payment::where('status', 'paid')
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)->sum('amount');
        $expensesMonth   = Expense::whereMonth('date', $today->month)
            ->whereYear('date', $today->year)->sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->count();

        // Médico — only compute when needed
        $myAppointmentsToday = 0;
        $myAppointmentsMonth = 0;
        $myPatientsMonth     = 0;

        if ($user->hasRole('medico')) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $myAppointmentsToday = Appointment::where('doctor_id', $doctor->id)
                    ->whereDate('scheduled_at', $today)->count();
                $myAppointmentsMonth = Appointment::where('doctor_id', $doctor->id)
                    ->whereMonth('scheduled_at', $today->month)
                    ->whereYear('scheduled_at', $today->year)->count();
                $myPatientsMonth = Appointment::where('doctor_id', $doctor->id)
                    ->whereMonth('scheduled_at', $today->month)
                    ->whereYear('scheduled_at', $today->year)
                    ->distinct('patient_id')->count('patient_id');
            }
        }

        return compact(
            'appointmentsToday', 'patientsThisMonth', 'availableDoctors', 'pendingAppointments',
            'revenueToday', 'revenueMonth', 'expensesMonth', 'pendingPayments',
            'myAppointmentsToday', 'myAppointmentsMonth', 'myPatientsMonth',
        );
    }
}; ?>

<div wire:poll.30s class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

    @role('admin')
        {{-- Admin: visão completa --}}
        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex-shrink-0">
                <x-heroicon-o-calendar-days class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Consultas Hoje') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $appointmentsToday }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex-shrink-0">
                <x-heroicon-o-users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Novos Pacientes (mês)') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $patientsThisMonth }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex-shrink-0">
                <x-heroicon-o-user-circle class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Médicos Disponíveis') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $availableDoctors }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex-shrink-0">
                <x-heroicon-o-banknotes class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Receita do Dia') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                    R$ {{ number_format($revenueToday, 2, ',', '.') }}
                </p>
            </div>
        </div>
    @endrole

    @role('medico')
        {{-- Médico: KPIs pessoais --}}
        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex-shrink-0">
                <x-heroicon-o-calendar-days class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Minhas Consultas Hoje') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $myAppointmentsToday }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex-shrink-0">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Minhas Consultas (mês)') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $myAppointmentsMonth }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex-shrink-0">
                <x-heroicon-o-users class="w-6 h-6 text-violet-600 dark:text-violet-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Pacientes Atendidos (mês)') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $myPatientsMonth }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex-shrink-0">
                <x-heroicon-o-user-circle class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Médicos Disponíveis') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $availableDoctors }}</p>
            </div>
        </div>
    @endrole

    @role('recepcionista')
        {{-- Recepcionista: foco operacional --}}
        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex-shrink-0">
                <x-heroicon-o-calendar-days class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Consultas Hoje') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $appointmentsToday }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex-shrink-0">
                <x-heroicon-o-users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Novos Pacientes (mês)') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $patientsThisMonth }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex-shrink-0">
                <x-heroicon-o-user-circle class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Médicos Disponíveis') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $availableDoctors }}</p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex-shrink-0">
                <x-heroicon-o-clock class="w-6 h-6 text-orange-600 dark:text-orange-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Ag. Pendentes') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $pendingAppointments }}</p>
            </div>
        </div>
    @endrole

    @role('financeiro')
        {{-- Financeiro: foco em receitas/despesas --}}
        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex-shrink-0">
                <x-heroicon-o-banknotes class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Receita do Dia') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                    R$ {{ number_format($revenueToday, 2, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex-shrink-0">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Receita do Mês') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                    R$ {{ number_format($revenueMonth, 2, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 flex-shrink-0">
                <x-heroicon-o-arrow-trending-down class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Despesas do Mês') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                    R$ {{ number_format($expensesMonth, 2, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="card flex items-center gap-4">
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex-shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Pagamentos Pendentes') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $pendingPayments }}</p>
            </div>
        </div>
    @endrole

</div>
