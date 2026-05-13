<?php

use App\Actions\Clinica\Doctors\CreateDoctorAction;
use App\Actions\Clinica\Doctors\DeleteDoctorAction;
use App\Actions\Clinica\Doctors\UpdateDoctorAction;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
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

    public string  $formUserId       = '';
    public string  $formSpecialty    = '';
    public string  $formCrm          = '';
    public string  $formDepartmentId = '';
    public bool    $formIsAvailable  = true;

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formUserId', 'formSpecialty', 'formCrm', 'formDepartmentId', 'editingId', 'confirmId']);
        $this->formIsAvailable = true;
        $this->showForm        = true;
    }

    public function openEdit(string $id): void
    {
        $doc = Doctor::findOrFail($id);
        $this->editingId       = $id;
        $this->formUserId      = $doc->user_id;
        $this->formSpecialty   = $doc->specialty;
        $this->formCrm         = $doc->crm;
        $this->formDepartmentId = $doc->department_id ?? '';
        $this->formIsAvailable = $doc->is_available;
        $this->showForm        = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $rules = [
            'formSpecialty'    => ['required', 'string', 'max:100'],
            'formCrm'          => ['required', 'string', 'max:20'],
            'formDepartmentId' => ['nullable', 'exists:departments,id'],
        ];

        if (! $this->editingId) {
            $rules['formUserId'] = ['required', 'exists:users,id'];
            $rules['formCrm'][]  = \Illuminate\Validation\Rule::unique('doctors', 'crm');
        }

        $this->validate($rules);

        $deptId = $this->formDepartmentId ?: null;

        if ($this->editingId) {
            $doc = Doctor::findOrFail($this->editingId);
            app(UpdateDoctorAction::class)->handle($doc, $this->formSpecialty, $this->formCrm, $deptId, $this->formIsAvailable);
            $msg = "Dr. {$doc->user->name} atualizado.";
        } else {
            $doc = app(CreateDoctorAction::class)->handle($this->formUserId, $this->formSpecialty, $this->formCrm, $deptId);
            $msg = "Médico cadastrado com sucesso.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $doc = Doctor::with('user')->findOrFail($this->confirmId);
        try {
            app(DeleteDoctorAction::class)->handle($doc);
            session()->flash('success', "Dr. {$doc->user->name} excluído.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['doctor'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $doctors = Doctor::with(['user', 'department'])
            ->withCount('appointments')
            ->when($this->search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                ->orWhere('crm', 'like', "%{$this->search}%")
                ->orWhere('specialty', 'like', "%{$this->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $users       = $this->showForm ? User::whereDoesntHave('doctor')->orderBy('name')->get() : collect();
        $departments = $this->showForm ? Department::where('is_active', true)->orderBy('name')->get() : collect();

        return compact('doctors', 'users', 'departments');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('Médicos') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Gerencie o corpo clínico da instituição.') }}</p>
        </div>
        @can('create', \App\Models\Doctor::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />{{ __('Novo médico') }}
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
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Buscar por nome, CRM ou especialidade...') }}" class="input" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Médico') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('CRM') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Especialidade') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Dept.') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Consultas') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Disponível') }}</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($doctors as $doc)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ $doc->user->initials() }}
                                </div>
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $doc->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $doc->crm }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $doc->specialty }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $doc->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $doc->appointments_count }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $doc->is_available ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                {{ $doc->is_available ? __('Sim') : __('Não') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $doc)
                                    <button wire:click="openEdit('{{ $doc->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $doc)
                                    @if($confirmId === $doc->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">{{ __('Confirmar?') }}</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">{{ __('Sim') }}</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">{{ __('Não') }}</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $doc->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">{{ __('Nenhum médico encontrado.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($doctors->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $doctors->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? __('Editar médico') : __('Novo médico') }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    @if(! $editingId)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Usuário') }}</label>
                            <select wire:model="formUserId" class="input">
                                <option value="">— Selecione —</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                            @error('formUserId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Especialidade') }}</label>
                            <input wire:model="formSpecialty" type="text" class="input" />
                            @error('formSpecialty')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('CRM') }}</label>
                            <input wire:model="formCrm" type="text" class="input" />
                            @error('formCrm')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Departamento') }}</label>
                        <select wire:model="formDepartmentId" class="input">
                            <option value="">— Nenhum —</option>
                            @foreach($departments as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($editingId)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="formIsAvailable" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ __('Disponível para consultas') }}</span>
                        </label>
                    @endif
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">{{ __('Cancelar') }}</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>{{ __('Salvar') }}</span><span wire:loading>{{ __('Salvando...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
