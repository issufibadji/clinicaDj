@if(session('success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-5 flex items-center gap-3 p-4 rounded-xl
                bg-emerald-50 dark:bg-emerald-900/30
                border border-emerald-200 dark:border-emerald-800
                text-emerald-700 dark:text-emerald-400 text-sm">
        <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
        <span class="flex-1">{{ session('success') }}</span>
        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition-colors">
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 6000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-5 flex items-center gap-3 p-4 rounded-xl
                bg-red-50 dark:bg-red-900/30
                border border-red-200 dark:border-red-800
                text-red-700 dark:text-red-400 text-sm">
        <x-heroicon-o-x-circle class="w-5 h-5 flex-shrink-0" />
        <span class="flex-1">{{ session('error') }}</span>
        <button @click="show = false" class="text-red-500 hover:text-red-700 transition-colors">
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </button>
    </div>
@endif

@if(session('warning'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-5 flex items-center gap-3 p-4 rounded-xl
                bg-amber-50 dark:bg-amber-900/30
                border border-amber-200 dark:border-amber-800
                text-amber-700 dark:text-amber-400 text-sm">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 flex-shrink-0" />
        <span class="flex-1">{{ session('warning') }}</span>
        <button @click="show = false" class="text-amber-500 hover:text-amber-700 transition-colors">
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </button>
    </div>
@endif
