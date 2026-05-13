<?php

use App\Models\Doctor;
use Livewire\Volt\Component;

new class extends Component
{
    public function with(): array
    {
        $doctors = Doctor::with(['user', 'department'])
            ->where('is_available', true)
            ->orderBy('created_at')
            ->get();

        return compact('doctors');
    }
}; ?>

<div class="card h-full flex flex-col">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
        <x-heroicon-o-user-circle class="w-4 h-4 text-amber-500" />
        Médicos Disponíveis
    </h2>

    @if($doctors->isEmpty())
        <div class="flex-1 flex flex-col items-center justify-center text-center py-6">
            <x-heroicon-o-user-circle class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" />
            <p class="text-sm text-slate-400">Nenhum médico disponível.</p>
        </div>
    @else
        <div class="flex-1" x-data="{ current: 0, total: {{ $doctors->count() }} }">

            @foreach($doctors as $i => $doctor)
                <div x-show="current === {{ $i }}" x-cloak class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ $doctor->user->initials() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $doctor->user->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $doctor->specialty }}</p>
                            @if($doctor->department)
                                <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $doctor->department->name }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Disponível
                    </span>
                </div>
            @endforeach

            @if($doctors->count() > 1)
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button @click="current = (current - 1 + total) % total"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                    <span class="text-xs text-slate-400" x-text="`${current + 1} / ${total}`"></span>
                    <button @click="current = (current + 1) % total"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                </div>
            @endif

        </div>
    @endif
</div>
