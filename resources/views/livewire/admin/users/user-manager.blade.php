<?php

use App\Actions\Admin\Users\CreateUserAction;
use App\Actions\Admin\Users\DeleteUserAction;
use App\Actions\Admin\Users\ResetUserPasswordAction;
use App\Actions\Admin\Users\ToggleUserStatusAction;
use App\Actions\Admin\Users\UpdateUserAction;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterRole   = '';
    public string $filterStatus = '';

    public bool   $showForm   = false;
    public ?string $editingId = null;
    public ?string $confirmId = null;

    // modal reset senha
    public ?string $resetPassword = null;

    // form
    public string $formName     = '';
    public string $formEmail    = '';
    public string $formPassword = '';
    public string $formRole     = '';
    public bool   $formActive   = true;

    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedFilterRole(): void   { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formName', 'formEmail', 'formPassword', 'formRole', 'editingId', 'confirmId']);
        $this->formActive = true;
        $this->showForm   = true;
    }

    public function openEdit(string $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId    = $id;
        $this->formName     = $user->name;
        $this->formEmail    = $user->email;
        $this->formPassword = '';
        $this->formRole     = $user->getRoleNames()->first() ?? '';
        $this->formActive   = $user->is_active;
        $this->showForm     = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $rules = [
            'formName'  => ['required', 'string', 'max:255'],
            'formEmail' => ['required', 'email', 'max:255',
                'unique:users,email' . ($this->editingId ? ",{$this->editingId}" : '')],
            'formRole'  => ['required', 'string', 'exists:roles,name'],
        ];

        if (! $this->editingId) {
            $rules['formPassword'] = ['required', 'string', 'min:8'];
        } elseif ($this->formPassword) {
            $rules['formPassword'] = ['string', 'min:8'];
        }

        $this->validate($rules);

        $data = [
            'name'      => $this->formName,
            'email'     => $this->formEmail,
            'password'  => $this->formPassword,
            'is_active' => $this->formActive,
        ];

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            app(UpdateUserAction::class)->handle($user, $data, $this->formRole);
            $msg = "Usuário \"{$this->formName}\" atualizado.";
        } else {
            app(CreateUserAction::class)->handle($data, $this->formRole);
            $msg = "Usuário \"{$this->formName}\" criado.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function toggleStatus(string $id): void
    {
        $target = User::findOrFail($id);
        try {
            app(ToggleUserStatusAction::class)->handle(auth()->user(), $target);
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['user'][0]);
        }
    }

    public function resetUserPassword(string $id): void
    {
        $user = User::findOrFail($id);
        $this->resetPassword = app(ResetUserPasswordAction::class)->handle($user);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $target = User::findOrFail($this->confirmId);
        try {
            app(DeleteUserAction::class)->handle(auth()->user(), $target);
            session()->flash('success', "Usuário \"{$target->name}\" excluído.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['user'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $users = User::with('roles')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterRole, fn($q) => $q->role($this->filterRole))
            ->when($this->filterStatus === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(15);

        $roles = Role::orderBy('level')->get();

        return compact('users', 'roles');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Usuários</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Gerencie os usuários do sistema.</p>
        </div>
        @can('create', \App\Models\User::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Novo usuário
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

    {{-- Filtros --}}
    <div class="card mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nome ou e-mail..." class="input text-sm" />
            <select wire:model.live="filterRole" class="input text-sm">
                <option value="">Todos os papéis</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="input text-sm">
                <option value="">Todos os status</option>
                <option value="active">Ativos</option>
                <option value="inactive">Inativos</option>
            </select>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuário</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Papel</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 w-36"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ $user->initials() }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800 dark:text-slate-100">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @php $role = $user->getRoleNames()->first(); @endphp
                            @if($role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 capitalize">
                                    {{ $role }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                         {{ $user->is_active
                                             ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                             : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                                {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if($confirmId === $user->id)
                                <div class="flex items-center gap-1.5 justify-end">
                                    <span class="text-xs text-slate-500">Confirmar exclusão?</span>
                                    <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                    <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                </div>
                            @else
                                <div class="flex items-center gap-1 justify-end">
                                    @can('update', $user)
                                        <button wire:click="openEdit('{{ $user->id }}')" title="Editar"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </button>
                                        <button wire:click="toggleStatus('{{ $user->id }}')" title="{{ $user->is_active ? 'Desativar' : 'Ativar' }}"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                            <x-heroicon-o-power class="w-4 h-4" />
                                        </button>
                                        <button wire:click="resetUserPassword('{{ $user->id }}')" title="Resetar senha"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                            <x-heroicon-o-key class="w-4 h-4" />
                                        </button>
                                    @endcan
                                    @can('delete', $user)
                                        <button wire:click="confirmDelete('{{ $user->id }}')" title="Excluir"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endcan
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-slate-400">Nenhum usuário encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Modal: criar/editar usuário --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar usuário' : 'Novo usuário' }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome completo</label>
                        <input wire:model="formName" type="text" class="input" />
                        @error('formName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">E-mail</label>
                        <input wire:model="formEmail" type="email" class="input" />
                        @error('formEmail')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Senha {{ $editingId ? '(deixe em branco para manter)' : '' }}
                        </label>
                        <input wire:model="formPassword" type="password" class="input" autocomplete="new-password" />
                        @error('formPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Papel</label>
                        <select wire:model="formRole" class="input">
                            <option value="">Selecione...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('formRole')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="formActive" type="checkbox" id="formActive"
                               class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
                        <label for="formActive" class="text-sm text-slate-700 dark:text-slate-300 cursor-pointer">Usuário ativo</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>Salvar</span>
                        <span wire:loading>Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: senha resetada --}}
    @if($resetPassword)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('resetPassword', null)"></div>
            <div class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <x-heroicon-o-key class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Senha resetada</h2>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Nova senha gerada (copie agora):</p>
                <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-100 dark:bg-slate-700 font-mono text-sm text-slate-800 dark:text-slate-100 break-all">
                    {{ $resetPassword }}
                </div>
                <button wire:click="$set('resetPassword', null)" class="btn-primary w-full mt-4 py-2 text-sm">
                    Entendido
                </button>
            </div>
        </div>
    @endif
</div>
