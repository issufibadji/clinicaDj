<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url] public string $entity = '';
    #[Url] public string $event  = '';
    #[Url] public string $from   = '';
    #[Url] public string $to     = '';

    public ?array $selectedAudit = null;

    private static array $entityLabels = [
        'App\\Models\\User'          => 'Usuário',
        'App\\Models\\MenuItem'      => 'Menu',
        'App\\Models\\SystemSetting' => 'Configuração',
    ];

    private static array $eventLabels = [
        'created'  => 'Criado',
        'updated'  => 'Atualizado',
        'deleted'  => 'Excluído',
        'restored' => 'Restaurado',
    ];

    private static array $eventColors = [
        'created'  => 'text-emerald-700 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-900/30',
        'updated'  => 'text-amber-700 bg-amber-50 dark:text-amber-400 dark:bg-amber-900/30',
        'deleted'  => 'text-red-700 bg-red-50 dark:text-red-400 dark:bg-red-900/30',
        'restored' => 'text-blue-700 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/30',
    ];

    public function updatedEntity(): void { $this->resetPage(); }
    public function updatedEvent(): void  { $this->resetPage(); }
    public function updatedFrom(): void   { $this->resetPage(); }
    public function updatedTo(): void     { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['entity', 'event', 'from', 'to']);
        $this->resetPage();
    }

    public function showDiff(int $id): void
    {
        $audit = Audit::with('user')->findOrFail($id);

        $old  = $audit->old_values ?? [];
        $new  = $audit->new_values ?? [];
        $diff = array_keys(array_diff_assoc($new, $old));

        $this->selectedAudit = [
            'id'           => $audit->id,
            'event'        => $audit->event,
            'event_label'  => self::$eventLabels[$audit->event] ?? $audit->event,
            'event_color'  => self::$eventColors[$audit->event] ?? '',
            'entity_label' => self::$entityLabels[$audit->auditable_type] ?? class_basename($audit->auditable_type),
            'auditable_id' => $audit->auditable_id,
            'user_name'    => $audit->user?->name ?? '(sistema)',
            'ip_address'   => $audit->ip_address,
            'created_at'   => $audit->created_at->format('d/m/Y H:i:s'),
            'old_values'   => $old,
            'new_values'   => $new,
            'changed_keys' => $diff,
        ];
    }

    public function closeDiff(): void
    {
        $this->selectedAudit = null;
    }

    public function exportJson(): mixed
    {
        $data = $this->buildQuery()->get()->map(fn($a) => [
            'id'         => $a->id,
            'event'      => $a->event,
            'entity'     => class_basename($a->auditable_type),
            'entity_id'  => $a->auditable_id,
            'user'       => $a->user?->name,
            'old_values' => $a->old_values,
            'new_values' => $a->new_values,
            'ip_address' => $a->ip_address,
            'created_at' => $a->created_at?->toIso8601String(),
        ]);

        return response()->streamDownload(
            fn() => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            'auditoria-' . now()->format('Ymd-His') . '.json',
            ['Content-Type' => 'application/json']
        );
    }

    private function buildQuery()
    {
        return Audit::with('user')
            ->when($this->entity, fn($q) => $q->where('auditable_type', $this->entity))
            ->when($this->event,  fn($q) => $q->where('event', $this->event))
            ->when($this->from,   fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,     fn($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest();
    }

    public function with(): array
    {
        return [
            'audits'       => $this->buildQuery()->paginate(15),
            'totalCount'   => $this->buildQuery()->count(),
            'entityLabels' => self::$entityLabels,
            'eventLabels'  => self::$eventLabels,
            'eventColors'  => self::$eventColors,
        ];
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Log de Auditoria</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Histórico de todas as alterações registradas no sistema.
            </p>
        </div>
        <button wire:click="exportJson"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium
                       bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                       text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700
                       transition-colors">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
            Exportar JSON
        </button>
    </div>

    {{-- Filtros --}}
    <div class="card mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Entidade</label>
                <select wire:model.live="entity" class="input text-sm">
                    <option value="">Todas</option>
                    @foreach($entityLabels as $class => $label)
                        <option value="{{ $class }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Evento</label>
                <select wire:model.live="event" class="input text-sm">
                    <option value="">Todos</option>
                    @foreach($eventLabels as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">De</label>
                <input wire:model.live="from" type="date" class="input text-sm" />
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Até</label>
                <input wire:model.live="to" type="date" class="input text-sm" />
            </div>

        </div>

        @if($entity || $event || $from || $to)
            <div class="mt-3 flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $totalCount }} {{ $totalCount === 1 ? 'registro encontrado' : 'registros encontrados' }}
                </span>
                <button wire:click="clearFilters"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                    Limpar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- Tabela --}}
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Data / Hora</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Evento</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Entidade</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Usuário</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP</th>
                        <th class="px-5 py-3 w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($audits as $audit)
                        @php
                            $eLabel = $entityLabels[$audit->auditable_type] ?? class_basename($audit->auditable_type);
                            $evColor = $eventColors[$audit->event] ?? 'text-slate-600 bg-slate-100';
                            $evLabel = $eventLabels[$audit->event] ?? $audit->event;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $audit->created_at->format('d/m/Y') }}
                                </span>
                                <span class="text-slate-400 dark:text-slate-500 text-xs ml-1">
                                    {{ $audit->created_at->format('H:i:s') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $evColor }}">
                                    {{ $evLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-slate-700 dark:text-slate-300">{{ $eLabel }}</span>
                                <span class="block text-xs text-slate-400 dark:text-slate-500 font-mono truncate max-w-[140px]">
                                    {{ $audit->auditable_id }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($audit->user)
                                    <span class="text-slate-700 dark:text-slate-300">{{ $audit->user->name }}</span>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500">{{ $audit->user->email }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic text-xs">sistema</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $audit->ip_address ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <button wire:click="showDiff({{ $audit->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium
                                               text-slate-600 dark:text-slate-400
                                               hover:bg-primary-50 dark:hover:bg-primary-900/30
                                               hover:text-primary-700 dark:hover:text-primary-400 transition-colors">
                                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                    Diff
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <x-heroicon-o-clipboard-document-list class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                                <p class="text-slate-400 dark:text-slate-500 text-sm">Nenhum registro de auditoria encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audits->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">
                {{ $audits->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de diff --}}
    @if($selectedAudit)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.closeDiff()">

            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                 wire:click="closeDiff"></div>

            <div class="relative w-full max-w-3xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                        border border-slate-200 dark:border-slate-700 overflow-hidden
                        max-h-[90vh] flex flex-col">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4
                            border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                     {{ $selectedAudit['event_color'] }}">
                            {{ $selectedAudit['event_label'] }}
                        </span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                            {{ $selectedAudit['entity_label'] }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">#{{ $selectedAudit['id'] }}</span>
                    </div>
                    <button wire:click="closeDiff"
                            class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>

                {{-- Meta --}}
                <div class="flex flex-wrap items-center gap-5 px-6 py-2.5
                            bg-slate-50 dark:bg-slate-800/60
                            border-b border-slate-100 dark:border-slate-700
                            text-xs text-slate-500 dark:text-slate-400 flex-shrink-0">
                    <span class="flex items-center gap-1.5">
                        <x-heroicon-o-user class="w-3.5 h-3.5" />
                        {{ $selectedAudit['user_name'] }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <x-heroicon-o-clock class="w-3.5 h-3.5" />
                        {{ $selectedAudit['created_at'] }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <x-heroicon-o-globe-alt class="w-3.5 h-3.5" />
                        {{ $selectedAudit['ip_address'] ?? '—' }}
                    </span>
                </div>

                {{-- Diff body --}}
                <div class="overflow-y-auto flex-1 p-6">
                    @if(empty($selectedAudit['old_values']) && empty($selectedAudit['new_values']))
                        <p class="text-center text-sm text-slate-400 py-8">
                            Nenhum valor registrado para este evento.
                        </p>
                    @else
                        <div class="grid grid-cols-2 gap-4">

                            {{-- Antes --}}
                            <div>
                                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Antes</h3>
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                    @if(empty($selectedAudit['old_values']))
                                        <div class="p-4 text-xs text-slate-400 italic bg-white dark:bg-slate-800">— registro novo —</div>
                                    @else
                                        @foreach($selectedAudit['old_values'] as $key => $val)
                                            @php $changed = in_array($key, $selectedAudit['changed_keys']); @endphp
                                            <div class="flex items-start gap-2 px-3 py-2
                                                        {{ $changed ? 'bg-red-50 dark:bg-red-900/20' : 'bg-white dark:bg-slate-800' }}
                                                        border-b border-slate-100 dark:border-slate-700/60 last:border-0">
                                                <span class="text-xs font-mono font-medium text-slate-500 min-w-[90px] pt-0.5 flex-shrink-0">{{ $key }}</span>
                                                <span class="text-xs font-mono break-all {{ $changed ? 'text-red-700 dark:text-red-400' : 'text-slate-700 dark:text-slate-300' }}">
                                                    {{ is_null($val) ? 'null' : (is_bool($val) ? ($val ? 'true' : 'false') : $val) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            {{-- Depois --}}
                            <div>
                                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Depois</h3>
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                    @if(empty($selectedAudit['new_values']))
                                        <div class="p-4 text-xs text-slate-400 italic bg-white dark:bg-slate-800">— registro excluído —</div>
                                    @else
                                        @foreach($selectedAudit['new_values'] as $key => $val)
                                            @php $changed = in_array($key, $selectedAudit['changed_keys']); @endphp
                                            <div class="flex items-start gap-2 px-3 py-2
                                                        {{ $changed ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-white dark:bg-slate-800' }}
                                                        border-b border-slate-100 dark:border-slate-700/60 last:border-0">
                                                <span class="text-xs font-mono font-medium text-slate-500 min-w-[90px] pt-0.5 flex-shrink-0">{{ $key }}</span>
                                                <span class="text-xs font-mono break-all {{ $changed ? 'text-emerald-700 dark:text-emerald-400 font-semibold' : 'text-slate-700 dark:text-slate-300' }}">
                                                    {{ is_null($val) ? 'null' : (is_bool($val) ? ($val ? 'true' : 'false') : $val) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                        </div>

                        @if(!empty($selectedAudit['changed_keys']))
                            <div class="mt-4 flex items-center gap-2 flex-wrap">
                                <span class="text-xs text-slate-400">Campos alterados:</span>
                                @foreach($selectedAudit['changed_keys'] as $changedKey)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30
                                                 text-amber-700 dark:text-amber-400 text-xs font-mono font-medium">
                                        {{ $changedKey }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>
