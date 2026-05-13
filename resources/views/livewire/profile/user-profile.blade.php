<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use OwenIt\Auditing\Models\Audit;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $user   = Auth::user();
        $audits = Audit::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return compact('user', 'audits');
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Meu Perfil</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Informações da sua conta e atividade recente.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Painel esquerdo: identidade --}}
        <div class="space-y-5">

            {{-- Card principal --}}
            <div class="card overflow-hidden p-0">
                {{-- Banner --}}
                <div class="h-24 bg-gradient-to-br from-primary-500 to-primary-700"></div>

                {{-- Avatar + info --}}
                <div class="px-5 pb-5">
                    <div class="-mt-10 mb-4">
                        @if($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}"
                                 class="w-20 h-20 rounded-full object-cover ring-4 ring-white dark:ring-slate-800"
                                 alt="{{ $user->name }}">
                        @else
                            <div class="w-20 h-20 rounded-full bg-primary-600 ring-4 ring-white dark:ring-slate-800
                                        flex items-center justify-center text-white text-2xl font-bold">
                                {{ $user->initials() }}
                            </div>
                        @endif
                    </div>

                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $user->email }}</p>

                    @php $role = $user->getRoleNames()->first(); @endphp
                    @if($role)
                        <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium
                                     bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 capitalize">
                            {{ $role }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Detalhes --}}
            <div class="card space-y-3">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Informações</h3>

                <div class="flex items-center gap-3">
                    <x-heroicon-o-phone class="w-4 h-4 text-slate-400 flex-shrink-0" />
                    <span class="text-sm text-slate-600 dark:text-slate-400">
                        {{ $user->phone ?: '—' }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <x-heroicon-o-shield-check class="w-4 h-4 flex-shrink-0
                        {{ $user->hasTwoFactorEnabled() ? 'text-emerald-500' : 'text-slate-400' }}" />
                    <span class="text-sm {{ $user->hasTwoFactorEnabled()
                        ? 'text-emerald-600 dark:text-emerald-400 font-medium'
                        : 'text-slate-500 dark:text-slate-400' }}">
                        2FA {{ $user->hasTwoFactorEnabled() ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <x-heroicon-o-check-badge class="w-4 h-4 flex-shrink-0
                        {{ $user->hasVerifiedEmail() ? 'text-emerald-500' : 'text-amber-400' }}" />
                    <span class="text-sm {{ $user->hasVerifiedEmail()
                        ? 'text-emerald-600 dark:text-emerald-400'
                        : 'text-amber-600 dark:text-amber-400' }}">
                        E-mail {{ $user->hasVerifiedEmail() ? 'verificado' : 'não verificado' }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400 flex-shrink-0" />
                    <span class="text-sm text-slate-500 dark:text-slate-400">
                        Membro desde {{ $user->created_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            {{-- Links rápidos --}}
            <div class="card space-y-1 p-3">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-2 mb-2">Ações rápidas</h3>

                <a href="{{ route('profile.settings') }}"
                   wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300
                          hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">
                    <x-heroicon-o-user-circle class="w-4 h-4 text-slate-400" />
                    Editar informações
                </a>

                <a href="{{ route('profile.settings', ['tab' => 'security']) }}"
                   wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300
                          hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">
                    <x-heroicon-o-lock-closed class="w-4 h-4 text-slate-400" />
                    Alterar senha
                </a>

                <a href="{{ route('profile.settings', ['tab' => '2fa']) }}"
                   wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300
                          hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-slate-400" />
                    Autenticação 2FA
                </a>
            </div>
        </div>

        {{-- Painel direito: atividade --}}
        <div class="lg:col-span-2">
            <div class="card">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">Atividade recente</h3>

                @if($audits->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <x-heroicon-o-clock class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" />
                        <p class="text-sm text-slate-400">Nenhuma atividade registrada.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($audits as $audit)
                            @php
                                $eventColors = [
                                    'created' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'updated' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'deleted' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                ];
                                $eventLabels = [
                                    'created' => 'Criado',
                                    'updated' => 'Atualizado',
                                    'deleted' => 'Excluído',
                                ];
                                $color = $eventColors[$audit->event] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
                                $label = $eventLabels[$audit->event] ?? $audit->event;
                                $model = class_basename($audit->auditable_type ?? '—');
                            @endphp
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0 {{ $color }}">
                                    {{ $label }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ $model }}
                                        @if($audit->new_values)
                                            <span class="text-xs text-slate-400 ml-1">
                                                ({{ implode(', ', array_keys((array) $audit->new_values)) }})
                                            </span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $audit->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
