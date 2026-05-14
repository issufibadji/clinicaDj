<?php

use App\Actions\Impersonation\StopImpersonation;
use App\Exceptions\ImpersonationException;
use App\Models\ImpersonationLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $filterAdmin    = '';
    public string $filterTarget   = '';
    public string $filterStatus   = '';
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';

    public ?string $expandedId = null;

    public function updatedFilterAdmin(): void    { $this->resetPage(); }
    public function updatedFilterTarget(): void   { $this->resetPage(); }
    public function updatedFilterStatus(): void   { $this->resetPage(); }
    public function updatedFilterDateFrom(): void { $this->resetPage(); }
    public function updatedFilterDateTo(): void   { $this->resetPage(); }

    public function toggleExpand(string $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function forceStop(string $logId): void
    {
        $log = ImpersonationLog::findOrFail($logId);

        if ($log->ended_at !== null) {
            session()->flash('error', __('Esta sessão já foi encerrada.'));
            return;
        }

        $log->update([
            'ended_at'   => now(),
            'end_reason' => 'force_stopped_by_admin',
        ]);

        // If this is the current active session, also log out the impersonator
        if (session('impersonation_log_id') === $logId) {
            try {
                app(StopImpersonation::class)->handle('force_stopped_by_admin');
            } catch (ImpersonationException) {
                // Already ended above — clear session manually
                session()->forget([
                    'impersonating', 'impersonation_log_id',
                    'original_user_id', 'original_profile_id',
                    'impersonation_started', 'impersonation_expires',
                ]);
            }
        }

        session()->flash('success', __('Sessão de impersonação encerrada com sucesso.'));
    }

    public function with(): array
    {
        $logs = ImpersonationLog::with(['admin', 'target'])
            ->when($this->filterAdmin, fn($q) => $q->where('admin_id', $this->filterAdmin))
            ->when($this->filterTarget, fn($q) => $q->where('target_id', $this->filterTarget))
            ->when($this->filterStatus === 'active', fn($q) => $q->whereNull('ended_at'))
            ->when($this->filterStatus === 'ended', fn($q) => $q->whereNotNull('ended_at'))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('started_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('started_at', '<=', $this->filterDateTo))
            ->latest('started_at')
            ->paginate(20);

        $admins  = User::role('admin')->orderBy('name')->get(['id', 'name']);
        $targets = User::whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                       ->orderBy('name')
                       ->get(['id', 'name']);

        return compact('logs', 'admins', 'targets');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Histórico de Impersonação</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Auditoria de acessos administrativos em nome de outros usuários.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400">
            <x-heroicon-o-exclamation-circle class="w-4 h-4 flex-shrink-0" />{{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <select wire:model.live="filterAdmin" class="input text-sm">
                <option value="">Todos os admins</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterTarget" class="input text-sm">
                <option value="">Todos os usuários</option>
                @foreach($targets as $target)
                    <option value="{{ $target->id }}">{{ $target->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" class="input text-sm">
                <option value="">Todos os status</option>
                <option value="active">Ativa</option>
                <option value="ended">Encerrada</option>
            </select>

            <input wire:model.live.debounce.400ms="filterDateFrom" type="date"
                   class="input text-sm" title="Data início (de)" />

            <input wire:model.live.debounce.400ms="filterDateTo" type="date"
                   class="input text-sm" title="Data início (até)" />
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Admin</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuário Alvo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Início</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Duração</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Ações</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ $log->admin?->initials() ?? '?' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-slate-100 truncate text-xs">{{ $log->admin?->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ $log->target?->initials() ?? '?' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-slate-100 truncate text-xs">{{ $log->target?->name ?? '—' }}</p>
                                    <p class="text-slate-400 text-xs truncate capitalize hidden sm:block">{{ $log->target?->getRoleNames()->first() ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            <p class="text-xs text-slate-700 dark:text-slate-300">{{ $log->started_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $log->started_at->format('H:i:s') }}</p>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <span class="text-xs text-slate-600 dark:text-slate-400 font-mono">{{ $log->duration }}</span>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $log->actions_count }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($log->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                    ATIVA
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                    {{ match($log->end_reason) {
                                        'manual'                  => 'Encerrada',
                                        'timeout'                 => 'Expirada',
                                        'force_stopped_by_admin'  => 'Forçada',
                                        default                   => 'Encerrada',
                                    } }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1 justify-end">
                                <button wire:click="toggleExpand('{{ $log->id }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                                        title="{{ $expandedId === $log->id ? 'Recolher' : 'Expandir' }}">
                                    <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform {{ $expandedId === $log->id ? 'rotate-180' : '' }}" />
                                </button>
                                @if($log->is_active)
                                    <button wire:click="forceStop('{{ $log->id }}')"
                                            wire:confirm="{{ __('Encerrar esta sessão de impersonação forçadamente?') }}"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                            title="Encerrar forçado">
                                        <x-heroicon-o-stop-circle class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Linha expandida com detalhes --}}
                    @if($expandedId === $log->id)
                        <tr class="bg-amber-50/60 dark:bg-amber-900/10">
                            <td colspan="7" class="px-6 py-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 mb-0.5 font-medium uppercase tracking-wider">Motivo</p>
                                        <p class="text-slate-700 dark:text-slate-300">{{ $log->notes ?: '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 mb-0.5 font-medium uppercase tracking-wider">IP do Admin</p>
                                        <p class="text-slate-700 dark:text-slate-300 font-mono">{{ $log->admin_ip ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 mb-0.5 font-medium uppercase tracking-wider">Encerramento</p>
                                        <p class="text-slate-700 dark:text-slate-300">
                                            {{ $log->ended_at ? $log->ended_at->format('d/m/Y H:i:s') : '—' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 mb-0.5 font-medium uppercase tracking-wider">Motivo encerramento</p>
                                        <p class="text-slate-700 dark:text-slate-300">
                                            {{ match($log->end_reason) {
                                                'manual'                 => 'Encerrado manualmente',
                                                'timeout'                => 'Expirado por tempo',
                                                'force_stopped_by_admin' => 'Encerrado forçadamente',
                                                null                     => '—',
                                                default                  => $log->end_reason,
                                            } }}
                                        </p>
                                    </div>
                                </div>
                                @if($log->admin_user_agent)
                                    <div class="mt-3">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-0.5 font-medium uppercase tracking-wider">User Agent</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono break-all">{{ $log->admin_user_agent }}</p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">
                            Nenhum registro de impersonação encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
