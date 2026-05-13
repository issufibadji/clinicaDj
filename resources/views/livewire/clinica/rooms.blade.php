<?php

use App\Actions\Clinica\Rooms\CreateRoomAction;
use App\Actions\Clinica\Rooms\DeleteRoomAction;
use App\Actions\Clinica\Rooms\UpdateRoomAction;
use App\Models\Department;
use App\Models\Room;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string  $search    = '';
    public bool    $showForm  = false;
    public ?string $editingId = null;
    public ?string $confirmId = null;

    public string  $formName         = '';
    public string  $formType         = '';
    public int     $formCapacity     = 1;
    public string  $formDepartmentId = '';
    public bool    $formIsActive     = true;

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formName', 'formType', 'formDepartmentId', 'editingId', 'confirmId']);
        $this->formCapacity = 1;
        $this->formIsActive = true;
        $this->showForm     = true;
    }

    public function openEdit(string $id): void
    {
        $room = Room::findOrFail($id);
        $this->editingId       = $id;
        $this->formName        = $room->name;
        $this->formType        = $room->type;
        $this->formCapacity    = $room->capacity;
        $this->formDepartmentId = $room->department_id ?? '';
        $this->formIsActive    = $room->is_active;
        $this->showForm        = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->validate([
            'formName'     => ['required', 'string', 'max:100'],
            'formType'     => ['required', 'string', 'max:50'],
            'formCapacity' => ['required', 'integer', 'min:1', 'max:500'],
            'formDepartmentId' => ['nullable', 'exists:departments,id'],
        ]);

        $deptId = $this->formDepartmentId ?: null;

        if ($this->editingId) {
            $room = Room::findOrFail($this->editingId);
            app(UpdateRoomAction::class)->handle($room, $this->formName, $this->formType, $this->formCapacity, $deptId, $this->formIsActive);
            $msg = "Sala \"{$room->name}\" atualizada.";
        } else {
            $room = app(CreateRoomAction::class)->handle($this->formName, $this->formType, $this->formCapacity, $deptId);
            $msg = "Sala \"{$room->name}\" criada.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $room = Room::findOrFail($this->confirmId);
        try {
            app(DeleteRoomAction::class)->handle($room);
            session()->flash('success', "Sala \"{$room->name}\" excluída.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['room'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $rooms = Room::with('department')
            ->withCount('appointments')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        $departments = $this->showForm ? Department::where('is_active', true)->orderBy('name')->get() : collect();

        return compact('rooms', 'departments');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Leitos e Salas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Gerencie as salas e leitos da clínica.</p>
        </div>
        @can('create', \App\Models\Room::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Nova sala
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
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar sala..." class="input" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Capac.</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Departamento</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($rooms as $room)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-100">{{ $room->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $room->type }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $room->capacity }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $room->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $room->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ $room->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $room)
                                    <button wire:click="openEdit('{{ $room->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $room)
                                    @if($confirmId === $room->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">Confirmar?</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $room->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">Nenhuma sala encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($rooms->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $rooms->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar sala' : 'Nova sala' }}
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
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo</label>
                            <input wire:model="formType" type="text" placeholder="ex: Consultório, UTI" class="input" />
                            @error('formType')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Capacidade</label>
                            <input wire:model="formCapacity" type="number" min="1" max="500" class="input" />
                            @error('formCapacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Departamento</label>
                        <select wire:model="formDepartmentId" class="input">
                            <option value="">— Nenhum —</option>
                            @foreach($departments as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($editingId)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="formIsActive" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Ativa</span>
                        </label>
                    @endif
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>Salvar</span><span wire:loading>Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
