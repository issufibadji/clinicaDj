<?php

use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;

new class extends Component
{
    public int $year;
    public int $month;

    public function mount(): void
    {
        $this->year  = now()->year;
        $this->month = now()->month;
    }

    public function prevMonth(): void
    {
        $d = Carbon::create($this->year, $this->month)->subMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->year, $this->month)->addMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
    }

    public function with(): array
    {
        $first = Carbon::create($this->year, $this->month, 1);

        $monthName = \Illuminate\Support\Str::ucfirst($first->translatedFormat('F'));

        // Locale-aware single-letter day abbreviations starting from Sunday
        $dayAbbrs = collect(range(0, 6))
            ->map(fn($i) => mb_strtoupper(mb_substr(
                Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays($i)->isoFormat('dd'),
                0, 1
            )))
            ->all();

        $appointmentDays = Appointment::whereYear('scheduled_at', $this->year)
            ->whereMonth('scheduled_at', $this->month)
            ->get(['scheduled_at'])
            ->map(fn($a) => $a->scheduled_at->day)
            ->unique()
            ->values()
            ->toArray();

        $blanksBefore = $first->dayOfWeek; // 0=Dom
        $daysInMonth  = $first->daysInMonth;
        $today        = today();
        $isThisMonth  = $this->year === $today->year && $this->month === $today->month;

        return compact('monthName', 'dayAbbrs', 'blanksBefore', 'daysInMonth', 'today', 'isThisMonth', 'appointmentDays');
    }
}; ?>

<div class="card h-full flex flex-col">
    {{-- Header navegação --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
            <x-heroicon-o-calendar class="w-4 h-4 text-primary-500" />
            {{ $monthName }} {{ $year }}
        </h2>
        <div class="flex gap-1">
            <button wire:click="prevMonth" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <x-heroicon-o-chevron-left class="w-4 h-4" />
            </button>
            <button wire:click="nextMonth" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <x-heroicon-o-chevron-right class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Cabeçalho dias da semana --}}
    <div class="grid grid-cols-7 mb-1">
        @foreach($dayAbbrs as $d)
            <div class="text-center text-[10px] font-semibold text-slate-400 dark:text-slate-500 py-0.5">{{ $d }}</div>
        @endforeach
    </div>

    {{-- Grade de dias --}}
    <div class="grid grid-cols-7 gap-0.5 flex-1">
        {{-- Células em branco antes do dia 1 --}}
        @for($i = 0; $i < $blanksBefore; $i++)
            <div></div>
        @endfor

        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $isToday  = $isThisMonth && $day === $today->day;
                $hasAppt  = in_array($day, $appointmentDays);
            @endphp
            <div class="relative flex flex-col items-center py-0.5">
                <span class="w-7 h-7 flex items-center justify-center text-xs rounded-full transition-colors
                    {{ $isToday
                        ? 'bg-primary-600 text-white font-bold'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    {{ $day }}
                </span>
                @if($hasAppt)
                    <span class="w-1 h-1 rounded-full mt-0.5
                        {{ $isToday ? 'bg-white' : 'bg-primary-500' }}"></span>
                @endif
            </div>
        @endfor
    </div>

    @if(count($appointmentDays) > 0)
        <p class="text-[10px] text-slate-400 text-right mt-2 pt-2 border-t border-slate-100 dark:border-slate-700">
            {{ count($appointmentDays) }} {{ count($appointmentDays) === 1 ? __('dia com consulta') : __('dias com consultas') }}
        </p>
    @endif
</div>
