<?php

use App\Actions\Admin\UserRoles\AssignUserRolesAction;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    public string  $search        = '';
    public ?string $selectedUserId = null;
    public array   $selectedRoles  = [];

    public ?string $flashMessage = null;

    public function selectUser(string $id): void
    {
        $this->selectedUserId = $id;
        $user = User::findOrFail($id);
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->flashMessage  = null;
    }

    public function toggleRole(string $roleName): void
    {
        if (! $this->selectedUserId) return;

        if (in_array($roleName, $this->selectedRoles)) {
            $this->selectedRoles = array_values(array_filter(
                $this->selectedRoles, fn($r) => $r !== $roleName
            ));
        } else {
            $this->selectedRoles[] = $roleName;
        }

        $user = User::findOrFail($this->selectedUserId);
        app(AssignUserRolesAction::class)->handle($user, $this->selectedRoles);

        $this->flashMessage = "Papéis de {$user->name} atualizados.";
    }

    public function with(): array
    {
        $users = User::with('roles')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('level')->get();
        $selectedUser = $this->selectedUserId ? User::with('roles')->find($this->selectedUserId) : null;

        return compact('users', 'roles', 'selectedUser');
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Vínculo Usuário — Papel</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Selecione um usuário à esquerda e atribua papéis à direita.
        </p>
    </div>

    @if($flashMessage)
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />{{ $flashMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Painel esquerdo: lista de usuários --}}
        <div class="card p-0 overflow-hidden flex flex-col">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                <input wire:model.live.debounce.250ms="search"
                       type="text" placeholder="Buscar usuário..."
                       class="input text-sm w-full" />
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60 overflow-y-auto max-h-[500px]">
                @forelse($users as $user)
                    <button wire:click="selectUser('{{ $user->id }}')"
                            class="w-full flex items-center gap-3 px-5 py-3.5 text-left transition-colors
                                   {{ $selectedUserId === $user->id
                                       ? 'bg-primary-50 dark:bg-primary-900/30'
                                       : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                        <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold text-white
                                    {{ $selectedUserId === $user->id ? 'bg-primary-600' : 'bg-slate-400 dark:bg-slate-600' }}">
                            {{ $user->initials() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate
                                      {{ $selectedUserId === $user->id
                                          ? 'text-primary-700 dark:text-primary-300'
                                          : 'text-slate-800 dark:text-slate-100' }}">
                                {{ $user->name }}
                            </p>
                            <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                        @if($selectedUserId === $user->id)
                            <x-heroicon-o-chevron-right class="w-4 h-4 text-primary-500 flex-shrink-0" />
                        @endif
                    </button>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-400">Nenhum usuário encontrado.</div>
                @endforelse
            </div>
        </div>

        {{-- Painel direito: papéis --}}
        <div class="card">
            @if(! $selectedUser)
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <x-heroicon-o-user-group class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" />
                    <p class="text-slate-400 dark:text-slate-500 text-sm">
                        Selecione um usuário para gerenciar seus papéis.
                    </p>
                </div>
            @else
                {{-- Cabeçalho do usuário selecionado --}}
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold">
                        {{ $selectedUser->initials() }}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $selectedUser->name }}</p>
                        <p class="text-xs text-slate-400">{{ $selectedUser->email }}</p>
                    </div>
                </div>

                {{-- Checkboxes de papéis --}}
                <div class="space-y-2">
                    @foreach($roles as $role)
                        <label class="flex items-center justify-between p-3 rounded-xl cursor-pointer
                                      border transition-colors
                                      {{ in_array($role->name, $selectedRoles)
                                          ? 'border-primary-200 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20'
                                          : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="text-sm font-medium capitalize
                                              {{ in_array($role->name, $selectedRoles)
                                                  ? 'text-primary-700 dark:text-primary-300'
                                                  : 'text-slate-700 dark:text-slate-300' }}">
                                        {{ $role->name }}
                                    </p>
                                    <p class="text-xs text-slate-400">Level {{ $role->level }}</p>
                                </div>
                            </div>
                            <input type="checkbox"
                                   wire:change="toggleRole('{{ $role->name }}')"
                                   {{ in_array($role->name, $selectedRoles) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
