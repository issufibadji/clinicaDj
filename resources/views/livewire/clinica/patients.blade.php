<?php

use App\Actions\Clinica\Patients\CreatePatientAction;
use App\Actions\Clinica\Patients\DeletePatientAction;
use App\Actions\Clinica\Patients\UpdatePatientAction;
use App\Models\Insurance;
use App\Models\Patient;
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

    public string  $formName        = '';
    public string  $formCpf         = '';
    public string  $formBirthDate   = '';
    public string  $formPhone       = '';
    public string  $formEmail       = '';
    public string  $formInsuranceId = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formName', 'formCpf', 'formBirthDate', 'formPhone', 'formEmail', 'formInsuranceId', 'editingId', 'confirmId']);
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $p = Patient::findOrFail($id);
        $this->editingId      = $id;
        $this->formName       = $p->name;
        $this->formCpf        = $p->cpf;
        $this->formBirthDate  = $p->birth_date->format('Y-m-d');
        $this->formPhone      = $p->phone;
        $this->formEmail      = $p->email ?? '';
        $this->formInsuranceId = $p->insurance_id ?? '';
        $this->showForm       = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->validate([
            'formName'      => ['required', 'string', 'max:100'],
            'formCpf'       => ['required', 'string', 'size:14'],
            'formBirthDate' => ['required', 'date'],
            'formPhone'     => ['required', 'string', 'max:20'],
            'formEmail'     => ['nullable', 'email', 'max:150'],
            'formInsuranceId' => ['nullable', 'exists:insurances,id'],
        ]);

        $insuranceId = $this->formInsuranceId ?: null;

        if ($this->editingId) {
            $p = Patient::findOrFail($this->editingId);
            app(UpdatePatientAction::class)->handle($p, $this->formName, $this->formCpf, $this->formBirthDate, $this->formPhone, $this->formEmail ?: null, $insuranceId);
            $msg = "Paciente \"{$p->name}\" atualizado.";
        } else {
            $p = app(CreatePatientAction::class)->handle($this->formName, $this->formCpf, $this->formBirthDate, $this->formPhone, $this->formEmail ?: null, $insuranceId);
            $msg = "Paciente \"{$p->name}\" cadastrado.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $p = Patient::findOrFail($this->confirmId);
        try {
            app(DeletePatientAction::class)->handle($p);
            session()->flash('success', "Paciente \"{$p->name}\" excluído.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['patient'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $patients = Patient::with('insurance')
            ->withCount('appointments')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('cpf', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->paginate(15);

        $insurances = Insurance::where('is_active', true)->orderBy('name')->get();

        return compact('patients', 'insurances');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Pacientes</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Cadastro de pacientes da clínica.</p>
        </div>
        @can('create', \App\Models\Patient::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Novo paciente
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
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nome, CPF ou telefone..." class="input" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">CPF</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nascimento</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Convênio</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Consultas</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($patients as $p)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $p->name }}</p>
                            <p class="text-xs text-slate-400">{{ $p->phone }}</p>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $p->cpf }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                            {{ $p->birth_date->format('d/m/Y') }}
                            <span class="text-xs text-slate-400">({{ $p->age() }} anos)</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $p->insurance?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $p->appointments_count }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $p)
                                    <button wire:click="openEdit('{{ $p->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $p)
                                    @if($confirmId === $p->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">Confirmar?</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $p->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">Nenhum paciente encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($patients->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $patients->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar paciente' : 'Novo paciente' }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome completo</label>
                        <input wire:model="formName" type="text" class="input" />
                        @error('formName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">CPF</label>
                            <input wire:model="formCpf" type="text" placeholder="000.000.000-00" class="input" maxlength="14" />
                            @error('formCpf')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data de nascimento</label>
                            <input wire:model="formBirthDate" type="date" class="input" />
                            @error('formBirthDate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Telefone</label>
                            <input wire:model="formPhone" type="text" placeholder="(11) 99999-9999" class="input" />
                            @error('formPhone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">E-mail</label>
                            <input wire:model="formEmail" type="email" class="input" />
                            @error('formEmail')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Convênio</label>
                        <select wire:model="formInsuranceId" class="input">
                            <option value="">— Particular —</option>
                            @foreach($insurances as $ins)
                                <option value="{{ $ins->id }}">{{ $ins->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>Salvar</span><span wire:loading>Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
