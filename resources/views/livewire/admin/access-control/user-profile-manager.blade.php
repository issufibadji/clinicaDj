<?php

use App\Actions\Admin\Profiles\CreateUserProfile;
use App\Actions\Admin\Profiles\DeleteUserProfile;
use App\Actions\Admin\Profiles\SetDefaultProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    public User $user;

    public bool $showCreateForm = false;
    public int|string $newRoleId = '';
    public string $newDisplayName = '';
    public string $newColor = '#10B981';

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function createProfile(): void
    {
        $this->validate([
            'newRoleId'      => 'required|exists:roles,id',
            'newDisplayName' => 'nullable|string|max:100',
            'newColor'       => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        try {
            app(CreateUserProfile::class)->handle($this->user, [
                'role_id'      => $this->newRoleId,
                'display_name' => $this->newDisplayName ?: null,
                'color'        => $this->newColor,
            ]);

            session()->flash('success', __('Perfil criado com sucesso.'));
            $this->reset(['showCreateForm', 'newRoleId', 'newDisplayName', 'newColor']);
        } catch (ValidationException $e) {
            $this->addError('newRoleId', collect($e->errors())->flatten()->first());
        }
    }

    public function setDefault(string $profileId): void
    {
        try {
            app(SetDefaultProfile::class)->handle($this->user, $profileId);
            session()->flash('success', __('Perfil padrão atualizado.'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteProfile(string $profileId): void
    {
        try {
            app(DeleteUserProfile::class)->handle($this->user, $profileId);
            session()->flash('success', __('Perfil removido.'));
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        }
    }

    public function with(): array
    {
        return [
            'profiles'       => $this->user->profiles()->with('role')->orderByDesc('is_default')->orderBy('created_at')->get(),
            'availableRoles' => Role::orderBy('name')->get(),
            'activeId'       => $this->user->active_profile_id,
            'audits'         => \OwenIt\Auditing\Models\Audit::where('auditable_type', UserProfile::class)
                ->whereIn('auditable_id', $this->user->profiles()->pluck('id'))
                ->latest()
                ->limit(20)
                ->get(),
        ];
    }
}; ?>

<div class="max-w-5xl mx-auto space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('admin.usuarios.index') }}" wire:navigate class="hover:text-primary-600 transition-colors">
                    {{ __('Usuários') }}
                </a>
                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                <span>{{ $user->name }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                {{ __('Perfis de :name', ['name' => $user->name]) }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $user->email }}</p>
        </div>
        <button wire:click="$set('showCreateForm', true)"
                class="btn-primary flex items-center gap-2 text-sm">
            <x-heroicon-o-plus class="w-4 h-4" />
            {{ __('Adicionar Perfil') }}
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30
                    border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 text-sm">
            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/30
                    border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 text-sm">
            <x-heroicon-o-exclamation-circle class="w-5 h-5 flex-shrink-0" />{{ session('error') }}
        </div>
    @endif

    {{-- Formulário novo perfil --}}
    @if($showCreateForm)
        <div class="card">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-4">
                {{ __('Adicionar perfil para :name', ['name' => $user->name]) }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Papel') }} <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="newRoleId" class="input">
                        <option value="">{{ __('Selecione...') }}</option>
                        @foreach($availableRoles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('newRoleId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Nome de exibição') }}
                    </label>
                    <input wire:model="newDisplayName" type="text" class="input"
                           placeholder="{{ __('Ex: Dr. João — Cardiologia') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Cor') }}
                    </label>
                    <div class="flex items-center gap-2">
                        <input wire:model="newColor" type="color"
                               class="h-9 w-14 rounded-lg border border-slate-300 dark:border-slate-600 cursor-pointer p-0.5 bg-white dark:bg-slate-700">
                        <input wire:model="newColor" type="text" class="input flex-1 font-mono text-sm"
                               placeholder="#10B981" maxlength="7">
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-5">
                <button wire:click="createProfile" class="btn-primary text-sm">{{ __('Criar Perfil') }}</button>
                <button wire:click="$set('showCreateForm', false)" class="btn-secondary text-sm">{{ __('Cancelar') }}</button>
            </div>
        </div>
    @endif

    {{-- Perfis --}}
    <div class="card !p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                {{ __('Perfis cadastrados') }}
                <span class="ml-1.5 text-sm font-normal text-slate-400">({{ $profiles->count() }})</span>
            </h2>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($profiles as $profile)
                <div class="flex items-center gap-4 px-6 py-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold flex-shrink-0"
                         style="background-color: {{ $profile->color }};">
                        {{ $profile->role_initial }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
                                {{ $profile->display_label }}
                            </p>
                            @if($profile->id === $activeId)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 uppercase tracking-wide">
                                    {{ __('ativo') }}
                                </span>
                            @endif
                            @if($profile->is_default)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 uppercase tracking-wide">
                                    {{ __('padrão') }}
                                </span>
                            @endif
                            @if(! $profile->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400 uppercase tracking-wide">
                                    {{ __('inativo') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 capitalize mt-0.5">
                            {{ $profile->role?->name }}
                            @if($profile->last_used_at)
                                · {{ __('usado :date', ['date' => $profile->last_used_at->diffForHumans()]) }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @if(! $profile->is_default && $profile->is_active)
                            <button wire:click="setDefault('{{ $profile->id }}')"
                                    class="btn-secondary text-xs py-1 px-2.5">
                                {{ __('Definir padrão') }}
                            </button>
                        @endif
                        @if($profile->id !== $activeId)
                            <button wire:click="deleteProfile('{{ $profile->id }}')"
                                    wire:confirm="{{ __('Remover este perfil?') }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-sm text-slate-400">
                    {{ __('Nenhum perfil cadastrado.') }}
                </div>
            @endforelse
        </div>
    </div>

    {{-- Histórico de trocas --}}
    @if($audits->isNotEmpty())
        <div class="card !p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('Histórico de perfis') }}</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($audits as $audit)
                    <div class="flex items-start gap-4 px-6 py-3">
                        <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-slate-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-medium">{{ $audit->event }}</span>
                                @if(isset($audit->new_values['new_role']))
                                    → <span class="text-primary-600 dark:text-primary-400">{{ $audit->new_values['new_role'] }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                {{ $audit->created_at->format('d/m/Y H:i') }}
                                @if($audit->user_id)
                                    · {{ __('por') }} {{ \App\Models\User::find($audit->user_id)?->name ?? '—' }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
