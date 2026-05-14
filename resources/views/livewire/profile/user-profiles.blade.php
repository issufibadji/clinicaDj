<?php

use App\Actions\Admin\Profiles\DeleteUserProfile;
use App\Actions\Admin\Profiles\SetDefaultProfile;
use App\Actions\Admin\Profiles\SwitchActiveProfile;
use App\Actions\Admin\Profiles\CreateUserProfile;
use App\Models\UserProfile;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component
{
    // Formulário de novo perfil
    public bool $showCreateForm = false;
    public int|string $newRoleId = '';
    public string $newDisplayName = '';
    public string $newColor = '#10B981';

    public function createProfile(): void
    {
        $this->validate([
            'newRoleId'      => 'required|exists:roles,id',
            'newDisplayName' => 'nullable|string|max:100',
            'newColor'       => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        try {
            app(CreateUserProfile::class)->handle(auth()->user(), [
                'role_id'      => $this->newRoleId,
                'display_name' => $this->newDisplayName ?: null,
                'color'        => $this->newColor,
            ]);

            session()->flash('success', __('Perfil criado com sucesso.'));
            $this->reset(['showCreateForm', 'newRoleId', 'newDisplayName', 'newColor']);
        } catch (ValidationException $e) {
            $this->addError('newRoleId', $e->getMessage());
        }
    }

    public function setDefault(string $profileId): void
    {
        try {
            app(SetDefaultProfile::class)->handle(auth()->user(), $profileId);
            session()->flash('success', __('Perfil padrão atualizado.'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function switchTo(string $profileId): void
    {
        try {
            app(SwitchActiveProfile::class)->handle(auth()->user(), $profileId);
            session()->flash('success', __('Perfil alterado com sucesso.'));
            $this->redirect(route('profile.profiles'), navigate: false);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteProfile(string $profileId): void
    {
        try {
            app(DeleteUserProfile::class)->handle(auth()->user(), $profileId);
            session()->flash('success', __('Perfil removido.'));
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        }
    }

    public function with(): array
    {
        return [
            'profiles'       => auth()->user()->profiles()->with('role')->orderByDesc('is_default')->orderBy('created_at')->get(),
            'availableRoles' => Role::orderBy('name')->get(),
            'activeId'       => auth()->user()->active_profile_id,
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('Meus Perfis') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                {{ __('Gerencie seus perfis de acesso ao sistema.') }}
            </p>
        </div>
        <button wire:click="$set('showCreateForm', true)"
                class="btn-primary flex items-center gap-2 text-sm">
            <x-heroicon-o-plus class="w-4 h-4" />
            {{ __('Novo Perfil') }}
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30
                    border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 text-sm">
            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/30
                    border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 text-sm">
            <x-heroicon-o-exclamation-circle class="w-5 h-5 flex-shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Formulário novo perfil --}}
    @if($showCreateForm)
        <div class="card">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-4">
                {{ __('Adicionar novo perfil') }}
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
                        {{ __('Cor identificadora') }}
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
                <button wire:click="createProfile" class="btn-primary text-sm">
                    {{ __('Criar Perfil') }}
                </button>
                <button wire:click="$set('showCreateForm', false)" class="btn-secondary text-sm">
                    {{ __('Cancelar') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Lista de perfis --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($profiles as $profile)
            @php $isActive = $profile->id === $activeId; @endphp

            <div class="card !p-0 overflow-hidden {{ ! $profile->is_active ? 'opacity-50' : '' }}">
                {{-- Barra de cor --}}
                <div class="h-1.5 w-full" style="background-color: {{ $profile->color }};"></div>

                <div class="p-5">
                    {{-- Badges --}}
                    <div class="flex items-center gap-1.5 mb-3 flex-wrap">
                        @if($isActive)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                         bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 uppercase tracking-wide">
                                {{ __('ativo') }}
                            </span>
                        @endif
                        @if($profile->is_default)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                         bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 uppercase tracking-wide">
                                {{ __('padrão') }}
                            </span>
                        @endif
                        @if(! $profile->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                         bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400 uppercase tracking-wide">
                                {{ __('inativo') }}
                            </span>
                        @endif
                    </div>

                    {{-- Avatar + nome --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold flex-shrink-0"
                             style="background-color: {{ $profile->color }};">
                            {{ $profile->role_initial }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
                                {{ $profile->display_label }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">
                                {{ $profile->role?->name }}
                            </p>
                        </div>
                    </div>

                    @if($profile->last_used_at)
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-4">
                            {{ __('Último uso: :date', ['date' => $profile->last_used_at->diffForHumans()]) }}
                        </p>
                    @endif

                    {{-- Ações --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        @if(! $isActive && $profile->is_active)
                            <button wire:click="switchTo('{{ $profile->id }}')"
                                    class="btn-primary text-xs py-1.5 px-3">
                                {{ __('Usar') }}
                            </button>
                        @endif
                        @if(! $profile->is_default && $profile->is_active)
                            <button wire:click="setDefault('{{ $profile->id }}')"
                                    class="btn-secondary text-xs py-1.5 px-3">
                                {{ __('Definir padrão') }}
                            </button>
                        @endif
                        @if(! $isActive)
                            <button wire:click="deleteProfile('{{ $profile->id }}')"
                                    wire:confirm="{{ __('Remover este perfil?') }}"
                                    class="ml-auto p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
