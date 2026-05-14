<?php

use App\Actions\Admin\Profiles\SwitchActiveProfile;
use Livewire\Volt\Component;

new class extends Component
{
    public function switchTo(string $profileId): void
    {
        $user = auth()->user();

        if ($user->active_profile_id === $profileId) {
            return;
        }

        app(SwitchActiveProfile::class)->handle($user, $profileId);

        session()->flash('success', __('Perfil alterado para :name.', [
            'name' => $user->fresh()->activeProfile?->display_label,
        ]));

        $this->redirect(route('dashboard'), navigate: false);
    }

    public function with(): array
    {
        $user = auth()->user();
        $active = $user->activeProfile?->load('role');

        return [
            'activeProfile' => $active,
            'otherProfiles' => $user->profiles()
                ->where('is_active', true)
                ->where('id', '!=', $user->active_profile_id)
                ->with('role')
                ->get(),
        ];
    }
}; ?>

<div>
@if($activeProfile || $otherProfiles->isNotEmpty())
    {{-- Perfil ativo --}}
    <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-700">
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">
            {{ __('Perfil ativo') }}
        </p>
        @if($activeProfile)
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background-color: {{ $activeProfile->color }};">
                    {{ $activeProfile->role_initial }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate leading-tight">
                        {{ $activeProfile->display_label }}
                    </p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 capitalize">
                        {{ $activeProfile->role?->name }}
                    </p>
                </div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide
                             bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 flex-shrink-0">
                    {{ __('ativo') }}
                </span>
            </div>
        @endif
    </div>

    {{-- Outros perfis --}}
    @if($otherProfiles->isNotEmpty())
        <div class="py-1 border-b border-slate-100 dark:border-slate-700">
            <p class="px-4 pt-1 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                {{ __('Trocar perfil') }}
            </p>
            @foreach($otherProfiles as $profile)
                <button wire:click="switchTo('{{ $profile->id }}')"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2.5 w-full px-4 py-2 text-sm transition-colors
                               text-slate-700 dark:text-slate-300
                               hover:bg-slate-50 dark:hover:bg-slate-700/60">
                    <div class="w-6 h-6 rounded-md flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         style="background-color: {{ $profile->color }};">
                        {{ $profile->role_initial }}
                    </div>
                    <span class="flex-1 text-left truncate">{{ $profile->display_label }}</span>
                    <span class="text-[11px] text-slate-400 capitalize">{{ $profile->role?->name }}</span>
                    <span wire:loading wire:target="switchTo('{{ $profile->id }}')">
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5 animate-spin text-slate-400" />
                    </span>
                </button>
            @endforeach
        </div>
    @endif
@endif
</div>
