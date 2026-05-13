<?php

use App\Actions\Admin\Permissions\CreatePermissionAction;
use App\Actions\Admin\Permissions\DeletePermissionAction;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search    = '';
    public bool   $showForm  = false;
    public ?int   $confirmId = null;

    // form fields
    public string $formName  = '';
    public string $formModule = '';
    public string $formGuard = 'web';

    public function updatedSearch(): void { $this->resetPage(); }

    public function openForm(): void
    {
        $this->reset(['formName', 'formModule', 'formGuard', 'confirmId']);
        $this->formGuard = 'web';
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
    }

    public function save(): void
    {
        $this->validate([
            'formName'   => ['required', 'string', 'regex:/^[a-z_]+\.[a-z_]+$/', 'unique:permissions,name'],
            'formModule' => ['required', 'string', 'max:50'],
        ], [
            'formName.regex' => 'Use o formato modulo.acao (ex: patients.view).',
            'formName.unique' => 'Esta permissão já existe.',
        ]);

        app(CreatePermissionAction::class)->handle($this->formName, $this->formModule, $this->formGuard);

        $this->showForm = false;
        session()->flash('success', "Permissão \"{$this->formName}\" criada.");
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmId = $id;
    }

    public function delete(): void
    {
        $permission = Permission::findOrFail($this->confirmId);

        try {
            app(DeletePermissionAction::class)->handle($permission);
            session()->flash('success', "Permissão \"{$permission->name}\" excluída.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['permission'][0]);
        }

        $this->confirmId = null;
    }

    public function with(): array
    {
        $permissions = Permission::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('module')
            ->orderBy('name')
            ->paginate(20);

        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');

        return compact('permissions', 'modules');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Permissões</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Gerencie as permissões do sistema.</p>
        </div>
        @can('create', \Spatie\Permission\Models\Permission::class)
            <button wire:click="openForm"
                    class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />
                Nova permissão
            </button>
        @endcan
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm
                    bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700
                    text-emerald-700 dark:text-emerald-400">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm
                    bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700
                    text-red-700 dark:text-red-400">
            <x-heroicon-o-exclamation-circle class="w-4 h-4 flex-shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Busca --}}
    <div class="card mb-5">
        <input wire:model.live.debounce.300ms="search" type="text"
               placeholder="Buscar permissão (ex: patients.view)..."
               class="input" />
    </div>

    {{-- Tabela agrupada por módulo --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Permissão</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Módulo</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Papéis vinculados</th>
                    <th class="px-5 py-3 w-16"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($permissions as $permission)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3 font-mono text-sm text-slate-700 dark:text-slate-300">
                            {{ $permission->name }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">
                                {{ $permission->module }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ $permission->roles->pluck('name')->join(', ') ?: '—' }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('delete', $permission)
                                @if($confirmId === $permission->id)
                                    <div class="flex items-center gap-2 justify-end">
                                        <span class="text-xs text-slate-500">Confirmar?</span>
                                        <button wire:click="delete"
                                                class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                        <button wire:click="$set('confirmId', null)"
                                                class="text-xs text-slate-500 hover:underline">Não</button>
                                    </div>
                                @else
                                    <button wire:click="confirmDelete({{ $permission->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-400">
                            Nenhuma permissão encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($permissions->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>

    {{-- Modal: nova permissão --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                        border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Nova permissão</h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Nome <span class="text-slate-400 font-normal">(formato: modulo.acao)</span>
                        </label>
                        <input wire:model="formName" type="text" placeholder="patients.view" class="input" />
                        @error('formName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Módulo</label>
                        <input wire:model="formModule" type="text" placeholder="patients" class="input" list="modules-list" />
                        <datalist id="modules-list">
                            @foreach($modules as $mod)<option value="{{ $mod }}">@endforeach
                        </datalist>
                        @error('formModule')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    <button wire:click="closeForm"
                            class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="save" class="btn-primary px-4 py-2 text-sm">Salvar</button>
                </div>
            </div>
        </div>
    @endif
</div>
