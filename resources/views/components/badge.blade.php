@props(['color' => 'gray'])

@php
$classes = match($color) {
    'green'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'red'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'amber'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'blue'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    default  => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>
