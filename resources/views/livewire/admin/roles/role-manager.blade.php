<?php

use App\Actions\Admin\Roles\CreateRoleAction;
use App\Actions\Admin\Roles\DeleteRoleAction;
use App\Actions\Admin\Roles\UpdateRoleAction;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    public bool    $showForm   = false;
    public ?int    $editingId  = null;
    public ?int    $confirmId  = null;

    // form
    public string $formName  = '';
    public int    $formLevel = 99;
    public array  $formPermissions = [];

    public function openCreate(): void
    {
        $this->reset(['formName', 'formLevel', 'formPermissions', 'editingId', 'confirmId']);
        $this->formLevel = 99;
        $this->showForm  = true;
    }

    public function openEdit(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->editingId       = $id;
        $this->formName        = $role->name;
        $this->formLevel       = $role->level;
        $this->formPermissions = $role->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->showForm        = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $rules = [
            'formLevel'       => ['required', 'integer', 'min:1', 'max:99'],
            'formPermissions' => ['array'],
        ];

        if (! $this->editingId) {
            $rules['formName'] = ['required', 'string', 'max:50', 'unique:roles,name'];
        }

        $this->validate($rules);

        if ($this->editingId) {
            $role = Role::findOrFail($this->editingId);
            app(UpdateRoleAction::class)->handle($role, $this->formLevel, $this->formPermissions);
            $msg = "Papel \"{$role->name}\" atualizado.";
        } else {
            $role = app(CreateRoleAction::class)->handle($this->formName, $this->formLevel, $this->formPermissions);
            $msg = "Papel \"{$role->name}\" criado.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(int $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $role = Role::findOrFail($this->confirmId);
        try {
            app(DeleteRoleAction::class)->handle($role);
            session()->flash('success', "Papel \"{$role->name}\" excluído.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['role'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('level')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        return compact('roles', 'permissions');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Papéis</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Gerencie os papéis e suas permissões.</p>
        </div>
        @can('create', \Spatie\Permission\Models\Role::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Novo papel
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

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Papel</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Level</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Permissões</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuários</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($roles as $role)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-medium text-slate-800 dark:text-slate-100 capitalize">{{ $role->name }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                         bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                {{ $role->level }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                            {{ $role->permissions->count() }} permissões
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                            {{ $role->users_count }} usuário(s)
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $role)
                                    <button wire:click="openEdit({{ $role->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $role)
                                    @if($confirmId === $role->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">Confirmar?</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete({{ $role->id }})"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">Nenhum papel encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal: criar/editar papel --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                        border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar papel' : 'Novo papel' }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                    {{-- Nome (só na criação) --}}
                    @if(! $editingId)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome</label>
                            <input wire:model="formName" type="text" placeholder="ex: enfermeiro" class="input" />
                            @error('formName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Nome</label>
                            <p class="text-slate-800 dark:text-slate-100 font-medium capitalize">{{ $formName }}</p>
                        </div>
                    @endif

                    {{-- Level --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Level <span class="text-slate-400 font-normal">(1 = mais privilegiado)</span>
                        </label>
                        <input wire:model="formLevel" type="number" min="1" max="99" class="input w-28" />
                        @error('formLevel')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Permissões agrupadas por módulo --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Permissões</label>
                        <div class="space-y-3">
                            @foreach($permissions as $module => $perms)
                                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/60 flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                            {{ $module }}
                                        </span>
                                    </div>
                                    <div class="px-4 py-2 grid grid-cols-2 gap-1.5">
                                        @foreach($perms as $perm)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox"
                                                       wire:model="formPermissions"
                                                       value="{{ $perm->id }}"
                                                       class="w-3.5 h-3.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
                                                <span class="text-xs font-mono text-slate-600 dark:text-slate-400">{{ $perm->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <button wire:click="closeForm"
                            class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>Salvar</span>
                        <span wire:loading>Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
