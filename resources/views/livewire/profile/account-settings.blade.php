<?php

use App\Actions\Profile\ConfirmTwoFactorAction;
use App\Actions\Profile\DisableTwoFactorAction;
use App\Actions\Profile\EnableTwoFactorAction;
use App\Actions\Profile\GenerateRecoveryCodesAction;
use App\Actions\Profile\UpdatePasswordAction;
use App\Actions\Profile\UpdateProfileInformationAction;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    #[Url(as: 'tab')]
    public string $tab = 'info';

    // ── Aba info ─────────────────────────────────────────────────────────────
    public string  $name  = '';
    public string  $email = '';
    public string  $phone = '';
    public         $avatarFile = null;

    // ── Aba security ──────────────────────────────────────────────────────────
    public string $currentPassword = '';
    public string $newPassword     = '';
    public string $confirmPassword = '';

    // ── Aba 2FA ───────────────────────────────────────────────────────────────
    public ?string $twoFactorQrSvg  = null;
    public ?string $twoFactorSecret = null;
    public string  $confirmCode     = '';
    public ?array  $recoveryCodes   = null;
    public string  $disablePassword = '';
    public string  $disableCode     = '';

    public ?string $flashMessage = null;
    public string  $flashType    = 'success';

    public function mount(): void
    {
        $user        = Auth::user();
        $this->name  = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    // ── Info ─────────────────────────────────────────────────────────────────

    public function saveInfo(): void
    {
        $this->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', \Illuminate\Validation\Rule::unique('users', 'email')->ignore(Auth::id())],
            'phone'      => ['nullable', 'string', 'max:20'],
            'avatarFile' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        app(UpdateProfileInformationAction::class)->handle(
            $user,
            $this->name,
            $this->email,
            $this->phone ?: null,
            $this->avatarFile,
        );

        $this->avatarFile = null;
        $this->flash('Informações atualizadas com sucesso.');
    }

    // ── Security ─────────────────────────────────────────────────────────────

    public function savePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required'],
            'newPassword'     => ['required', 'min:8', 'confirmed:confirmPassword'],
        ], [
            'newPassword.confirmed' => 'A confirmação de senha não corresponde.',
        ]);

        try {
            app(UpdatePasswordAction::class)->handle(
                Auth::user(),
                $this->currentPassword,
                $this->newPassword,
            );

            $this->reset(['currentPassword', 'newPassword', 'confirmPassword']);
            $this->flash('Senha atualizada com sucesso.');
        } catch (ValidationException $e) {
            $this->addError('currentPassword', $e->errors()['currentPassword'][0] ?? 'Erro.');
        }
    }

    // ── 2FA: Enable ──────────────────────────────────────────────────────────

    public function enableTwoFactor(): void
    {
        $user   = Auth::user();
        $result = app(EnableTwoFactorAction::class)->handle($user);

        $this->twoFactorSecret = $result['secret'];
        $this->twoFactorQrSvg  = $this->generateQrSvg($result['qrUrl']);
        $this->confirmCode     = '';
        $this->recoveryCodes   = null;
    }

    public function confirmTwoFactor(): void
    {
        $this->validate(['confirmCode' => ['required', 'digits:6']]);

        try {
            $codes = app(ConfirmTwoFactorAction::class)->handle(Auth::user(), $this->confirmCode);
            $this->recoveryCodes   = $codes;
            $this->twoFactorQrSvg  = null;
            $this->twoFactorSecret = null;
            $this->confirmCode     = '';
            $this->flash('2FA ativado! Guarde os códigos de recuperação.');
        } catch (ValidationException $e) {
            $this->addError('confirmCode', $e->errors()['confirmCode'][0] ?? 'Código inválido.');
        }
    }

    public function regenerateRecoveryCodes(): void
    {
        $codes = app(GenerateRecoveryCodesAction::class)->handle(Auth::user());
        $this->recoveryCodes = $codes;
        $this->flash('Códigos de recuperação regenerados. Guarde-os agora.');
    }

    public function disableTwoFactor(): void
    {
        $this->validate([
            'disablePassword' => ['required'],
            'disableCode'     => ['required', 'digits:6'],
        ]);

        try {
            app(DisableTwoFactorAction::class)->handle(Auth::user(), $this->disablePassword, $this->disableCode);
            $this->reset(['disablePassword', 'disableCode', 'twoFactorQrSvg', 'twoFactorSecret', 'recoveryCodes']);
            $this->flash('2FA desativado.', 'warning');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['disablePassword'])) $this->addError('disablePassword', $errors['disablePassword'][0]);
            if (isset($errors['disableCode']))     $this->addError('disableCode', $errors['disableCode'][0]);
        }
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function flash(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType    = $type;
    }

    private function generateQrSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(180),
            new SvgImageBackEnd(),
        );

        return (new Writer($renderer))->writeString($url);
    }

    public function with(): array
    {
        return ['user' => Auth::user()];
    }
}; ?>

<div x-data="{ tab: @entangle('tab') }">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Configurações da conta</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Gerencie suas informações, senha e segurança.</p>
    </div>

    {{-- Flash --}}
    @if($flashMessage)
        <div class="mb-5 flex items-center gap-2 p-3 rounded-xl text-sm
                    {{ $flashType === 'success'
                        ? 'bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400'
                        : 'bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-400' }}">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-slate-200 dark:border-slate-700 mb-6">
        <nav class="flex gap-1 -mb-px">
            @foreach([
                ['info',     'Informações',  'heroicon-o-user-circle'],
                ['security', 'Segurança',    'heroicon-o-lock-closed'],
                ['2fa',      'Dois Fatores', 'heroicon-o-shield-check'],
            ] as [$key, $label, $icon])
                <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'border-b-2 border-primary-600 text-primary-700 dark:text-primary-400'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                        class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors">
                    <x-dynamic-component :component="$icon" class="w-4 h-4" />
                    {{ $label }}
                    @if($key === '2fa' && $user->hasTwoFactorEnabled())
                        <span class="w-2 h-2 rounded-full bg-emerald-500 ml-0.5"></span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ── ABA INFO ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'info'" x-cloak>
        <div class="card max-w-xl">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-5">Informações pessoais</h2>

            <div class="space-y-4">
                {{-- Avatar preview --}}
                <div class="flex items-center gap-4">
                    @if($user->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}" class="w-16 h-16 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-600" alt="">
                    @else
                        <div class="w-16 h-16 rounded-full bg-primary-600 flex items-center justify-center text-white text-xl font-bold">
                            {{ $user->initials() }}
                        </div>
                    @endif

                    <div>
                        <label class="cursor-pointer">
                            <span class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600
                                         text-xs font-medium text-slate-700 dark:text-slate-300
                                         hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                Alterar foto
                            </span>
                            <input type="file" wire:model="avatarFile" class="sr-only" accept="image/*" />
                        </label>
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG. Máx. 2 MB.</p>
                        @error('avatarFile')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Nome --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome</label>
                    <input wire:model="name" type="text" class="input" />
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- E-mail --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">E-mail</label>
                    <input wire:model="email" type="email" class="input" />
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @if(! $user->hasVerifiedEmail())
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">E-mail não verificado.</p>
                    @endif
                </div>

                {{-- Telefone --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Telefone</label>
                    <input wire:model="phone" type="text" placeholder="(11) 99999-9999" class="input" />
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="saveInfo" wire:loading.attr="disabled"
                        class="btn-primary px-5 py-2 text-sm">
                    <span wire:loading.remove wire:target="saveInfo">Salvar</span>
                    <span wire:loading wire:target="saveInfo">Salvando...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── ABA SECURITY ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'security'" x-cloak>
        <div class="card max-w-xl">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-5">Alterar senha</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Senha atual</label>
                    <input wire:model="currentPassword" type="password" class="input" autocomplete="current-password" />
                    @error('currentPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nova senha</label>
                    <input wire:model="newPassword" type="password" class="input" autocomplete="new-password" />
                    @error('newPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirmar nova senha</label>
                    <input wire:model="confirmPassword" type="password" class="input" autocomplete="new-password" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="savePassword" wire:loading.attr="disabled"
                        class="btn-primary px-5 py-2 text-sm">
                    <span wire:loading.remove wire:target="savePassword">Atualizar senha</span>
                    <span wire:loading wire:target="savePassword">Atualizando...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── ABA 2FA ───────────────────────────────────────────────────────── --}}
    <div x-show="tab === '2fa'" x-cloak>
        <div class="card max-w-xl space-y-5">

            @if(! $user->hasTwoFactorEnabled() && ! $twoFactorQrSvg)
                {{-- Estado: desativado --}}
                <div class="flex flex-col items-center py-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
                        <x-heroicon-o-shield-exclamation class="w-8 h-8 text-slate-400" />
                    </div>
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">2FA desativado</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs mb-5">
                        Adicione uma camada extra de segurança usando um aplicativo autenticador.
                    </p>
                    <button wire:click="enableTwoFactor" wire:loading.attr="disabled"
                            class="btn-primary px-5 py-2 text-sm">
                        <span wire:loading.remove wire:target="enableTwoFactor">Ativar 2FA</span>
                        <span wire:loading wire:target="enableTwoFactor">Preparando...</span>
                    </button>
                </div>

            @elseif($twoFactorQrSvg)
                {{-- Estado: configurando --}}
                <div>
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">Configure o autenticador</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Escaneie o QR Code com Google Authenticator, Authy ou similar.
                    </p>
                </div>

                <div class="flex flex-col items-center gap-4">
                    <div class="p-3 bg-white rounded-xl border border-slate-200 inline-block">
                        {!! $twoFactorQrSvg !!}
                    </div>

                    <div class="w-full">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Ou insira o código manualmente:</p>
                        <code class="block px-3 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg font-mono text-sm
                                     text-slate-800 dark:text-slate-100 tracking-widest text-center select-all">
                            {{ $twoFactorSecret }}
                        </code>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Código de confirmação (6 dígitos)
                    </label>
                    <input wire:model="confirmCode" type="text" inputmode="numeric" maxlength="6"
                           placeholder="000000"
                           class="input font-mono text-center tracking-[0.5em] text-lg w-full" />
                    @error('confirmCode')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click="$set('twoFactorQrSvg', null)"
                            class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="confirmTwoFactor" wire:loading.attr="disabled"
                            class="btn-primary px-5 py-2 text-sm">
                        <span wire:loading.remove wire:target="confirmTwoFactor">Confirmar</span>
                        <span wire:loading wire:target="confirmTwoFactor">Verificando...</span>
                    </button>
                </div>

            @elseif($user->hasTwoFactorEnabled())
                {{-- Estado: ativo --}}
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20
                            border border-emerald-200 dark:border-emerald-700">
                    <x-heroicon-o-shield-check class="w-6 h-6 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                    <div>
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">2FA ativo</p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">
                            Ativado em {{ $user->two_factor_confirmed_at?->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                {{-- Códigos de recuperação --}}
                @if($recoveryCodes)
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2">
                            Guarde estes códigos de recuperação agora!
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mb-3">
                            Cada código pode ser usado uma única vez para acessar sua conta se perder o autenticador.
                        </p>
                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach($recoveryCodes as $code)
                                <code class="px-2 py-1 bg-white dark:bg-slate-800 rounded font-mono text-sm
                                             text-slate-800 dark:text-slate-200 border border-amber-200 dark:border-amber-700
                                             text-center select-all">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Códigos de recuperação</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ count($user->getRecoveryCodes()) }} código(s) disponível(is)
                            </p>
                        </div>
                        <button wire:click="regenerateRecoveryCodes" wire:loading.attr="disabled"
                                class="px-3 py-1.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600
                                       text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Regenerar
                        </button>
                    </div>
                @endif

                {{-- Desativar 2FA --}}
                <div x-data="{ showDisable: false }">
                    <button @click="showDisable = !showDisable"
                            class="text-sm text-red-600 dark:text-red-400 hover:underline">
                        Desativar 2FA
                    </button>

                    <div x-show="showDisable" x-cloak class="mt-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20
                              border border-red-200 dark:border-red-700 space-y-3">
                        <p class="text-xs text-red-700 dark:text-red-400 font-medium">
                            Confirme sua senha e o código atual do autenticador para desativar o 2FA.
                        </p>
                        <input wire:model="disablePassword" type="password" placeholder="Senha atual"
                               class="input text-sm" />
                        @error('disablePassword')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                        <input wire:model="disableCode" type="text" inputmode="numeric" maxlength="6"
                               placeholder="Código 2FA" class="input font-mono text-center tracking-widest text-sm" />
                        @error('disableCode')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                        <div class="flex gap-2 justify-end">
                            <button @click="showDisable = false"
                                    class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400
                                           hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button wire:click="disableTwoFactor"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700
                                           rounded-lg transition-colors">
                                Confirmar desativação
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
