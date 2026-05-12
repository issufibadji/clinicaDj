<?php

use App\Actions\Admin\System\SaveSystemSettingAction;
use App\Models\SystemSetting;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public array $values = [];

    // Agrupamento lógico das settings (chave → grupo)
    private const GROUPS = [
        'clinic_name'                  => 'Dados da Clínica',
        'clinic_cnpj'                  => 'Dados da Clínica',
        'clinic_phone'                 => 'Dados da Clínica',
        'clinic_address'               => 'Dados da Clínica',
        'allow_public_booking'         => 'Agendamento',
        'allow_patient_registration'   => 'Agendamento',
        'appointment_fee'              => 'Agendamento',
        'max_daily_appointments'       => 'Agendamento',
        'default_appointment_duration' => 'Agendamento',
        'email_notifications'          => 'Notificações',
    ];

    public function mount(): void
    {
        $this->values = SystemSetting::all()->pluck('value', 'key')->toArray();
    }

    public function save(string $key): void
    {
        try {
            app(SaveSystemSettingAction::class)->handle($key, $this->values[$key] ?? '');
            $label = SystemSetting::where('key', $key)->value('label');
            $this->dispatch('setting-saved', label: $label);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError($key, $e->errors()[$key][0] ?? 'Valor inválido.');
        }
    }

    public function toggle(string $key): void
    {
        $this->values[$key] = ($this->values[$key] === '1') ? '0' : '1';
        $this->save($key);
    }

    public function with(): array
    {
        $settings = SystemSetting::all();

        $groups = [];
        foreach ($settings as $setting) {
            $group = self::GROUPS[$setting->key] ?? 'Geral';
            $groups[$group][] = $setting;
        }

        return ['groups' => $groups];
    }
}; ?>

<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Configurações do Sistema</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Parâmetros globais da clínica. Alterações são salvas ao sair do campo.
            </p>
        </div>
    </div>

    {{-- Flash de confirmação --}}
    <div
        x-data="{ show: false, label: '' }"
        x-on:setting-saved.window="label = $event.detail.label; show = true; setTimeout(() => show = false, 2500)"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-end="opacity-0"
        class="mb-5 flex items-center gap-2 p-3 rounded-xl text-sm font-medium
               bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400
               border border-emerald-200 dark:border-emerald-700"
        style="display:none">
        <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />
        <span>"<span x-text="label"></span>" salvo com sucesso.</span>
    </div>

    {{-- Grupos --}}
    <div class="space-y-6">
        @foreach($groups as $groupName => $settings)
            <div class="card p-0 overflow-hidden">

                {{-- Cabeçalho do grupo --}}
                <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        {{ $groupName }}
                    </h2>
                </div>

                {{-- Settings --}}
                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($settings as $setting)
                        <div class="flex items-start gap-4 px-6 py-4">

                            {{-- Label + descrição --}}
                            <div class="flex-1 min-w-0">
                                <label for="s_{{ $setting->key }}"
                                       class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                        {{ $setting->description }}
                                    </p>
                                @endif
                                @error($setting->key)
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Input por tipo --}}
                            <div class="flex-shrink-0 flex items-center">

                                @if($setting->type === 'boolean')
                                    {{-- Toggle switch --}}
                                    <button
                                        wire:click="toggle('{{ $setting->key }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="toggle('{{ $setting->key }}')"
                                        title="{{ ($values[$setting->key] ?? '0') === '1' ? 'Desativar' : 'Ativar' }}"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                                               {{ ($values[$setting->key] ?? '0') === '1'
                                                    ? 'bg-primary-600'
                                                    : 'bg-slate-300 dark:bg-slate-600' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200
                                                     {{ ($values[$setting->key] ?? '0') === '1' ? 'translate-x-6' : 'translate-x-1' }}">
                                        </span>
                                    </button>
                                    <span class="ml-2.5 text-xs font-medium {{ ($values[$setting->key] ?? '0') === '1' ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500' }}">
                                        {{ ($values[$setting->key] ?? '0') === '1' ? 'Ativo' : 'Inativo' }}
                                    </span>

                                @elseif($setting->type === 'integer')
                                    {{-- Input numérico inteiro --}}
                                    <input
                                        id="s_{{ $setting->key }}"
                                        wire:model="values.{{ $setting->key }}"
                                        wire:blur="save('{{ $setting->key }}')"
                                        type="number" step="1" min="0"
                                        class="input w-28 text-right tabular-nums" />

                                @elseif($setting->type === 'decimal')
                                    {{-- Input numérico decimal --}}
                                    <input
                                        id="s_{{ $setting->key }}"
                                        wire:model="values.{{ $setting->key }}"
                                        wire:blur="save('{{ $setting->key }}')"
                                        type="number" step="0.01" min="0"
                                        class="input w-32 text-right tabular-nums" />

                                @else
                                    {{-- Input texto --}}
                                    <input
                                        id="s_{{ $setting->key }}"
                                        wire:model="values.{{ $setting->key }}"
                                        wire:blur="save('{{ $setting->key }}')"
                                        type="text"
                                        class="input w-64" />
                                @endif

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
