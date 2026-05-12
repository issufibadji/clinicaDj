@props(['type' => 'info', 'dismissible' => true])

@php
[$colorClasses, $icon] = match($type) {
    'success' => [
        'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400',
        'heroicon-o-check-circle',
    ],
    'warning' => [
        'bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400',
        'heroicon-o-exclamation-triangle',
    ],
    'error' => [
        'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400',
        'heroicon-o-x-circle',
    ],
    default => [
        'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400',
        'heroicon-o-information-circle',
    ],
};
@endphp

<div x-data="{ show: true }"
     x-show="show"
     {{ $attributes->merge(['class' => "flex items-start gap-3 p-4 rounded-xl border text-sm $colorClasses"]) }}>
    <x-dynamic-component :component="$icon" class="w-5 h-5 flex-shrink-0 mt-0.5" />
    <div class="flex-1">{{ $slot }}</div>
    @if($dismissible)
        <button @click="show = false" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity">
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </button>
    @endif
</div>
