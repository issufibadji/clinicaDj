<?php

use App\Actions\Admin\Profiles\SetDefaultProfile;
use App\Actions\Admin\Profiles\SwitchActiveProfile;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public bool $rememberChoice = false;

    public function mount(): void
    {
        $user = auth()->user();

        // Redireciona direto se tem apenas 1 perfil ativo
        $activeProfiles = $user->profiles()->where('is_active', true)->with('role')->get();

        if ($activeProfiles->count() <= 1) {
            $profile = $activeProfiles->first()
                ?? $user->defaultProfile
                ?? null;

            if ($profile) {
                app(SwitchActiveProfile::class)->handle($user, $profile->id);
            }

            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function selectProfile(string $profileId): void
    {
        $user = auth()->user();

        if ($this->rememberChoice) {
            app(SetDefaultProfile::class)->handle($user, $profileId);
        }

        app(SwitchActiveProfile::class)->handle($user, $profileId);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function with(): array
    {
        return [
            'profiles' => auth()->user()
                ->profiles()
                ->where('is_active', true)
                ->with('role')
                ->get(),
        ];
    }
}; ?>

<div x-data="{ selected: null }" class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            {{ __('Selecione seu perfil') }}
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ __('Olá, :name! Com qual perfil deseja entrar?', ['name' => auth()->user()->name]) }}
        </p>
    </div>

    {{-- Cards de perfil --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4"
         :class="{{ $profiles->count() }} >= 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2'">

        @foreach($profiles as $profile)
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 style="transition-delay: {{ $loop->index * 75 }}ms">

                <button
                    wire:click="selectProfile('{{ $profile->id }}')"
                    wire:loading.attr="disabled"
                    :class="selected === '{{ $profile->id }}' ? 'ring-2 ring-primary-500 scale-[1.02]' : 'hover:scale-[1.02] hover:shadow-lg'"
                    @click="selected = '{{ $profile->id }}'"
                    class="relative w-full text-left rounded-2xl border border-slate-200 dark:border-slate-700
                           bg-white dark:bg-slate-800 overflow-hidden transition-all duration-200
                           focus:outline-none cursor-pointer shadow-sm">

                    {{-- Barra colorida no topo --}}
                    <div class="h-1.5 w-full" style="background-color: {{ $profile->color }};"></div>

                    {{-- Badge padrão --}}
                    @if($profile->is_default)
                        <div class="absolute top-4 right-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                         bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 uppercase tracking-wide">
                                {{ __('Padrão') }}
                            </span>
                        </div>
                    @endif

                    <div class="p-5">
                        {{-- Avatar do papel --}}
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg font-bold mb-4 mx-auto"
                             style="background-color: {{ $profile->color }};">
                            {{ $profile->role_initial }}
                        </div>

                        {{-- Informações --}}
                        <div class="text-center">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate leading-tight">
                                {{ $profile->display_label }}
                            </p>
                            <span class="inline-flex items-center mt-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium
                                         bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 capitalize">
                                {{ $profile->role?->name ?? '-' }}
                            </span>
                        </div>

                        {{-- Último uso --}}
                        @if($profile->last_used_at)
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center mt-3">
                                {{ __('Último uso: :date', ['date' => $profile->last_used_at->diffForHumans()]) }}
                            </p>
                        @endif

                        {{-- Botão entrar --}}
                        <div class="mt-4">
                            <div class="w-full py-1.5 rounded-lg text-center text-xs font-semibold
                                        text-white transition-colors"
                                 style="background-color: {{ $profile->color }};">
                                <span wire:loading.remove wire:target="selectProfile('{{ $profile->id }}')">
                                    {{ __('Entrar') }}
                                </span>
                                <span wire:loading wire:target="selectProfile('{{ $profile->id }}')">
                                    {{ __('Entrando...') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        @endforeach
    </div>

    {{-- Checkbox lembrar escolha --}}
    <label class="flex items-center gap-2.5 cursor-pointer group">
        <input wire:model="rememberChoice"
               type="checkbox"
               class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500/20">
        <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors">
            {{ __('Lembrar minha escolha como padrão') }}
        </span>
    </label>

</div>
