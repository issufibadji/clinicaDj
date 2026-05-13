<?php

use App\Actions\Clinica\Departments\CreateDepartmentAction;
use App\Actions\Clinica\Departments\DeleteDepartmentAction;
use App\Actions\Clinica\Departments\UpdateDepartmentAction;
use App\Models\Department;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string  $search     = '';
    public bool    $showForm   = false;
    public ?string $editingId  = null;
    public ?string $confirmId  = null;

    public string $formName        = '';
    public string $formDescription = '';
    public bool   $formIsActive    = true;

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formName', 'formDescription', 'editingId', 'confirmId']);
        $this->formIsActive = true;
        $this->showForm     = true;
    }

    public function openEdit(string $id): void
    {
        $dep = Department::findOrFail($id);
        $this->editingId       = $id;
        $this->formName        = $dep->name;
        $this->formDescription = $dep->description ?? '';
        $this->formIsActive    = $dep->is_active;
        $this->showForm        = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->validate([
            'formName' => ['required', 'string', 'max:100'],
            'formDescription' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editingId) {
            $dep = Department::findOrFail($this->editingId);
            app(UpdateDepartmentAction::class)->handle($dep, $this->formName, $this->formDescription ?: null, $this->formIsActive);
            $msg = "Departamento \"{$dep->name}\" atualizado.";
        } else {
            $dep = app(CreateDepartmentAction::class)->handle($this->formName, $this->formDescription ?: null);
            $msg = "Departamento \"{$dep->name}\" criado.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $dep = Department::findOrFail($this->confirmId);
        try {
            app(DeleteDepartmentAction::class)->handle($dep);
            session()->flash('success', "Departamento \"{$dep->name}\" excluído.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['department'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $departments = Department::withCount(['rooms', 'doctors'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return compact('departments');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Departamentos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Gerencie os departamentos da clínica.</p>
        </div>
        @can('create', \App\Models\Department::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Novo departamento
            </button>
        @endcan
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

    <div class="card mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar departamento..." class="input" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Salas</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Médicos</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($departments as $dep)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $dep->name }}</p>
                            @if($dep->description)
                                <p class="text-xs text-slate-400 mt-0.5 truncate max-w-xs">{{ $dep->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $dep->rooms_count }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $dep->doctors_count }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $dep->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ $dep->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $dep)
                                    <button wire:click="openEdit('{{ $dep->id }}')"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $dep)
                                    @if($confirmId === $dep->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">Confirmar?</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $dep->id }}')"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">Nenhum departamento encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($departments->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $departments->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar departamento' : 'Novo departamento' }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome</label>
                        <input wire:model="formName" type="text" class="input" />
                        @error('formName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Descrição</label>
                        <textarea wire:model="formDescription" rows="3" class="input resize-none"></textarea>
                        @error('formDescription')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @if($editingId)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="formIsActive" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Ativo</span>
                        </label>
                    @endif
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>Salvar</span>
                        <span wire:loading>Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
