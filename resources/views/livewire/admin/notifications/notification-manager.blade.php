<?php

use App\Models\User;
use App\Notifications\ManualNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {

    public bool    $showForm    = false;
    public ?string $deleteBatch = null;

    // form fields
    public string $formTitle   = '';
    public string $formBody    = '';
    public string $formUrl     = '';
    public string $formIcon    = 'bell';
    public string $formColor   = 'blue';
    public array  $formUsers   = [];
    public bool   $formAll     = false;

    private static array $ICON_OPTIONS = [
        'bell'             => 'Sino',
        'calendar'         => 'Calendário',
        'banknotes'        => 'Pagamento',
        'information-circle' => 'Informação',
        'exclamation-triangle' => 'Aviso',
    ];

    private static array $COLOR_OPTIONS = [
        'blue'  => 'Azul',
        'green' => 'Verde',
        'red'   => 'Vermelho',
        'slate' => 'Cinza',
    ];

    public function openCreate(): void
    {
        $this->reset(['formTitle', 'formBody', 'formUrl', 'formUsers', 'formAll']);
        $this->formIcon  = 'bell';
        $this->formColor = 'blue';
        $this->showForm  = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function send(): void
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:150'],
            'formBody'  => ['required', 'string', 'max:500'],
            'formUrl'   => ['nullable', 'string', 'max:255'],
            'formIcon'  => ['required', 'in:' . implode(',', array_keys(self::$ICON_OPTIONS))],
            'formColor' => ['required', 'in:' . implode(',', array_keys(self::$COLOR_OPTIONS))],
        ]);

        if (! $this->formAll && empty($this->formUsers)) {
            $this->addError('formUsers', __('Selecione ao menos um destinatário.'));
            return;
        }

        $recipients = $this->formAll
            ? User::where('is_active', true)->get()
            : User::whereIn('id', $this->formUsers)->get();

        $batchId = (string) Str::uuid();

        Notification::send(
            $recipients,
            new ManualNotification(
                batchId: $batchId,
                title:   $this->formTitle,
                body:    $this->formBody,
                icon:    $this->formIcon,
                color:   $this->formColor,
                url:     $this->formUrl ?: '',
            )
        );

        $this->showForm = false;
        session()->flash('success', __('Notificação enviada para :count destinatário(s).', ['count' => $recipients->count()]));
    }

    public function confirmDeleteBatch(string $batchId): void
    {
        $this->deleteBatch = $batchId;
    }

    public function deleteBatch(): void
    {
        if (! $this->deleteBatch) return;

        DB::table('notifications')
            ->where('type', ManualNotification::class)
            ->whereJsonContains('data->batch_id', $this->deleteBatch)
            ->delete();

        $this->deleteBatch = null;
        session()->flash('success', __('Envio removido.'));
    }

    public function with(): array
    {
        $iconOptions  = self::$ICON_OPTIONS;
        $colorOptions = self::$COLOR_OPTIONS;
        $users        = User::orderBy('name')->get(['id', 'name', 'email']);

        $sends = DB::table('notifications')
            ->where('type', ManualNotification::class)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn($n) => json_decode($n->data, true)['batch_id'] ?? $n->id)
            ->map(fn($group) => (object) [
                'batch_id'   => json_decode($group->first()->data, true)['batch_id'] ?? null,
                'title'      => json_decode($group->first()->data, true)['title'] ?? '',
                'body'       => json_decode($group->first()->data, true)['body'] ?? '',
                'icon'       => json_decode($group->first()->data, true)['icon'] ?? 'bell',
                'color'      => json_decode($group->first()->data, true)['color'] ?? 'blue',
                'sent_at'    => $group->first()->created_at,
                'recipients' => $group->count(),
                'read'       => $group->whereNotNull('read_at')->count(),
            ])
            ->values()
            ->take(50);

        return compact('iconOptions', 'colorOptions', 'users', 'sends');
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('Notificações') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Envie notificações internas para os usuários.') }}</p>
        </div>
        <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
            <x-heroicon-o-paper-airplane class="w-4 h-4" />
            {{ __('Enviar notificação') }}
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm
                    bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700
                    text-emerald-700 dark:text-emerald-400">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />{{ session('success') }}
        </div>
    @endif

    {{-- Modal de envio --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-trap.noscroll="true">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg
                        border border-slate-200 dark:border-slate-700 overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ __('Enviar Notificação') }}
                    </h2>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">

                    {{-- Título --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            {{ __('Título') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="formTitle" type="text"
                               placeholder="{{ __('Ex: Reunião amanhã às 9h') }}"
                               class="input w-full" maxlength="150" />
                        @error('formTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Mensagem --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            {{ __('Mensagem') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="formBody" rows="3"
                                  placeholder="{{ __('Texto da notificação...') }}"
                                  class="input w-full resize-none" maxlength="500"></textarea>
                        @error('formBody') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Link --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            {{ __('Link (opcional)') }}
                        </label>
                        <input wire:model="formUrl" type="text"
                               placeholder="/clinica/agendamentos"
                               class="input w-full" />
                    </div>

                    {{-- Ícone + Cor --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                {{ __('Ícone') }}
                            </label>
                            <select wire:model="formIcon" class="input w-full">
                                @foreach($iconOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                {{ __('Cor') }}
                            </label>
                            <select wire:model="formColor" class="input w-full">
                                @foreach($colorOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Destinatários --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            {{ __('Destinatários') }} <span class="text-red-500">*</span>
                        </label>

                        <label class="flex items-center gap-2.5 mb-2 cursor-pointer">
                            <input wire:model.live="formAll" type="checkbox"
                                   class="rounded border-slate-300 dark:border-slate-600 text-primary-600" />
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ __('Enviar para todos os usuários ativos') }}
                            </span>
                        </label>

                        @if(!$formAll)
                            <div class="border border-slate-200 dark:border-slate-600 rounded-xl overflow-hidden max-h-40 overflow-y-auto">
                                @foreach($users as $user)
                                    <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/40 cursor-pointer border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                                        <input type="checkbox" wire:model="formUsers" value="{{ $user->id }}"
                                               class="rounded border-slate-300 dark:border-slate-600 text-primary-600" />
                                        <div>
                                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 leading-tight">{{ $user->name }}</p>
                                            <p class="text-xs text-slate-400 leading-tight">{{ $user->email }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        @error('formUsers') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <button wire:click="closeForm"
                            class="btn-secondary px-4 py-2 text-sm">{{ __('Cancelar') }}</button>
                    <button wire:click="send" wire:loading.attr="disabled"
                            class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                        <span wire:loading.remove wire:target="send">
                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                        </span>
                        <span wire:loading wire:target="send">
                            <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                        </span>
                        {{ __('Enviar') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal confirmação de exclusão --}}
    @if($deleteBatch)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm
                        border border-slate-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mb-4">
                    {{ __('Remover este envio e todas as notificações associadas?') }}
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('deleteBatch', null)"
                            class="btn-secondary px-4 py-2 text-sm">{{ __('Cancelar') }}</button>
                    <button wire:click="deleteBatch"
                            class="px-4 py-2 text-sm rounded-xl bg-red-600 hover:bg-red-700 text-white font-medium transition-colors">
                        {{ __('Remover') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Histórico de envios --}}
    @if($sends->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <x-heroicon-o-paper-airplane class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" />
            <p class="text-base font-medium text-slate-500 dark:text-slate-400">{{ __('Nenhuma notificação enviada') }}</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">{{ __('Clique em "Enviar notificação" para começar.') }}</p>
        </div>
    @else
        <div class="card p-0 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-700 text-xs font-semibold
                               text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        <th class="px-5 py-3 text-left">{{ __('Notificação') }}</th>
                        <th class="px-5 py-3 text-center hidden sm:table-cell">{{ __('Destinatários') }}</th>
                        <th class="px-5 py-3 text-center hidden md:table-cell">{{ __('Lidas') }}</th>
                        <th class="px-5 py-3 text-right hidden sm:table-cell">{{ __('Enviado em') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($sends as $send)
                        @php
                            $colorMap = [
                                'blue'  => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
                                'green' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400',
                                'red'   => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
                                'slate' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
                            ];
                            $iconClass = $colorMap[$send->color] ?? $colorMap['slate'];
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $iconClass }}">
                                        @if($send->icon === 'calendar')
                                            <x-heroicon-o-calendar class="w-4 h-4" />
                                        @elseif($send->icon === 'calendar-days')
                                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                                        @elseif($send->icon === 'banknotes')
                                            <x-heroicon-o-banknotes class="w-4 h-4" />
                                        @elseif($send->icon === 'information-circle')
                                            <x-heroicon-o-information-circle class="w-4 h-4" />
                                        @elseif($send->icon === 'exclamation-triangle')
                                            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                        @else
                                            <x-heroicon-o-bell class="w-4 h-4" />
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $send->title }}</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-xs">{{ $send->body }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center hidden sm:table-cell">
                                <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                    <x-heroicon-o-user-group class="w-3.5 h-3.5 text-slate-400" />
                                    {{ $send->recipients }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center hidden md:table-cell">
                                @if($send->recipients > 0)
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                                 {{ $send->read === $send->recipients
                                                     ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                     : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                        {{ $send->read }}/{{ $send->recipients }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right hidden sm:table-cell text-xs text-slate-400 dark:text-slate-500">
                                {{ \Carbon\Carbon::parse($send->sent_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="confirmDeleteBatch('{{ $send->batch_id }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50
                                               dark:hover:bg-red-900/20 transition-colors"
                                        title="{{ __('Remover envio') }}">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
