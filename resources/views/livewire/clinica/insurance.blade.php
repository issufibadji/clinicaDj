<?php

use App\Actions\Clinica\Insurances\CreateInsuranceAction;
use App\Actions\Clinica\Insurances\DeleteInsuranceAction;
use App\Actions\Clinica\Insurances\UpdateInsuranceAction;
use App\Models\Insurance;
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
    public string  $formPlanType     = '';
    public string  $formContactPhone = '';
    public bool    $formIsActive     = true;

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->authorize('create', \App\Models\Insurance::class);
        $this->reset(['formName', 'formPlanType', 'formContactPhone', 'editingId', 'confirmId']);
        $this->formIsActive = true;
        $this->showForm     = true;
    }

    public function openEdit(string $id): void
    {
        $this->authorize('update', Insurance::findOrFail($id));
        $ins = Insurance::findOrFail($id);
        $this->editingId        = $id;
        $this->formName         = $ins->name;
        $this->formPlanType     = $ins->plan_type;
        $this->formContactPhone = $ins->contact_phone ?? '';
        $this->formIsActive     = $ins->is_active;
        $this->showForm         = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->editingId
            ? $this->authorize('update', Insurance::findOrFail($this->editingId))
            : $this->authorize('create', \App\Models\Insurance::class);

        $this->validate([
            'formName'     => ['required', 'string', 'max:100'],
            'formPlanType' => ['required', 'string', 'max:50'],
            'formContactPhone' => ['nullable', 'string', 'max:20'],
        ]);

        if ($this->editingId) {
            $ins = Insurance::findOrFail($this->editingId);
            app(UpdateInsuranceAction::class)->handle($ins, $this->formName, $this->formPlanType, $this->formContactPhone ?: null, $this->formIsActive);
            $msg = "Convênio \"{$ins->name}\" atualizado.";
        } else {
            $ins = app(CreateInsuranceAction::class)->handle($this->formName, $this->formPlanType, $this->formContactPhone ?: null);
            $msg = "Convênio \"{$ins->name}\" criado.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void
    {
        $this->authorize('delete', Insurance::findOrFail($id));
        $this->confirmId = $id;
    }

    public function delete(): void
    {
        $ins = Insurance::findOrFail($this->confirmId);
        $this->authorize('delete', $ins);
        try {
            app(DeleteInsuranceAction::class)->handle($ins);
            session()->flash('success', "Convênio \"{$ins->name}\" excluído.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['insurance'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $insurances = Insurance::withCount('patients')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return compact('insurances');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('Convênios') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Gerencie os convênios médicos aceitos.') }}</p>
        </div>
        @can('create', \App\Models\Insurance::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />{{ __('Novo convênio') }}
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
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Buscar convênio...') }}" class="input" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Nome') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Tipo de plano') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Contato') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Pacientes') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Status') }}</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($insurances as $ins)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-100">{{ $ins->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $ins->plan_type }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $ins->contact_phone ?: '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $ins->patients_count }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $ins->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ $ins->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $ins)
                                    <button wire:click="openEdit('{{ $ins->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $ins)
                                    @if($confirmId === $ins->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">{{ __('Confirmar?') }}</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">{{ __('Sim') }}</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">{{ __('Não') }}</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $ins->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">{{ __('Nenhum convênio encontrado.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($insurances->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $insurances->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? __('Editar convênio') : __('Novo convênio') }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Nome') }}</label>
                        <input wire:model="formName" type="text" class="input" />
                        @error('formName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo de plano</label>
                        <input wire:model="formPlanType" type="text" placeholder="ex: Coletivo, Individual, Familiar" class="input" />
                        @error('formPlanType')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Telefone de contato</label>
                        <input wire:model="formContactPhone" type="text" placeholder="(11) 3000-0000" class="input" />
                    </div>
                    @if($editingId)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="formIsActive" class="w-4 h-4 rounded text-primary-600" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Ativo</span>
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
