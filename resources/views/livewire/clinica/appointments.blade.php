<?php

use App\Actions\Clinica\Appointments\CreateAppointmentAction;
use App\Actions\Clinica\Appointments\DeleteAppointmentAction;
use App\Actions\Clinica\Appointments\UpdateAppointmentAction;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Room;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string  $search       = '';
    public string  $filterStatus = '';
    public string  $filterDate   = '';
    public bool    $showForm     = false;
    public ?string $editingId    = null;
    public ?string $confirmId    = null;

    public string  $formPatientId   = '';
    public string  $formDoctorId    = '';
    public string  $formRoomId      = '';
    public string  $formScheduledAt = '';
    public string  $formStatus      = 'scheduled';
    public string  $formNotes       = '';

    private static array $STATUS_LABELS = [
        'scheduled'  => 'Agendado',
        'confirmed'  => 'Confirmado',
        'completed'  => 'Concluído',
        'cancelled'  => 'Cancelado',
    ];

    private static array $STATUS_COLORS = [
        'scheduled'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'confirmed'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'completed'  => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
        'cancelled'  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    ];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterDate(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->authorize('create', \App\Models\Appointment::class);
        $this->reset(['formPatientId', 'formDoctorId', 'formRoomId', 'formNotes', 'editingId', 'confirmId']);
        $this->formScheduledAt = now()->addDay()->format('Y-m-d\TH:i');
        $this->formStatus      = 'scheduled';
        $this->showForm        = true;
    }

    public function openEdit(string $id): void
    {
        $this->authorize('update', \App\Models\Appointment::findOrFail($id));
        $a = Appointment::findOrFail($id);
        $this->editingId      = $id;
        $this->formPatientId  = $a->patient_id;
        $this->formDoctorId   = $a->doctor_id;
        $this->formRoomId     = $a->room_id ?? '';
        $this->formScheduledAt = $a->scheduled_at->format('Y-m-d\TH:i');
        $this->formStatus     = $a->status;
        $this->formNotes      = $a->notes ?? '';
        $this->showForm       = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->editingId
            ? $this->authorize('update', Appointment::findOrFail($this->editingId))
            : $this->authorize('create', \App\Models\Appointment::class);

        $this->validate([
            'formPatientId'   => ['required', 'exists:patients,id'],
            'formDoctorId'    => ['required', 'exists:doctors,id'],
            'formRoomId'      => ['nullable', 'exists:rooms,id'],
            'formScheduledAt' => ['required', 'date', 'after:now'],
            'formStatus'      => ['required', 'in:scheduled,confirmed,completed,cancelled'],
            'formNotes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $roomId = $this->formRoomId ?: null;

        if ($this->editingId) {
            $a = Appointment::findOrFail($this->editingId);
            app(UpdateAppointmentAction::class)->handle($a, $this->formPatientId, $this->formDoctorId, $roomId, $this->formScheduledAt, $this->formStatus, $this->formNotes ?: null);
            $msg = __('Agendamento atualizado.');
        } else {
            app(CreateAppointmentAction::class)->handle($this->formPatientId, $this->formDoctorId, $roomId, $this->formScheduledAt, $this->formNotes ?: null);
            $msg = __('Agendamento criado.');
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void
    {
        $this->authorize('delete', Appointment::findOrFail($id));
        $this->confirmId = $id;
    }

    public function delete(): void
    {
        $a = Appointment::findOrFail($this->confirmId);
        $this->authorize('delete', $a);
        try {
            app(DeleteAppointmentAction::class)->handle($a);
            session()->flash('success', __('Agendamento excluído.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['appointment'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $statusLabels = self::$STATUS_LABELS;
        $statusColors = self::$STATUS_COLORS;

        $appointments = Appointment::with(['patient', 'doctor.user', 'room'])
            ->when($this->search, fn($q) => $q->whereHas('patient', fn($p) => $p->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('doctor.user', fn($u) => $u->where('name', 'like', "%{$this->search}%")))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDate, fn($q) => $q->whereDate('scheduled_at', $this->filterDate))
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15);

        $patients = $this->showForm ? Patient::orderBy('name')->get() : collect();
        $doctors  = $this->showForm ? Doctor::with('user')->orderBy('created_at')->get() : collect();
        $rooms    = $this->showForm ? Room::where('is_active', true)->orderBy('name')->get() : collect();

        return compact('appointments', 'patients', 'doctors', 'rooms', 'statusLabels', 'statusColors');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('Agendamentos') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Gestão de consultas e procedimentos.') }}</p>
        </div>
        @can('create', \App\Models\Appointment::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />{{ __('Novo agendamento') }}
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

    <div class="card mb-4 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Buscar paciente ou médico...') }}" class="input flex-1 min-w-0" />
        <select wire:model.live="filterStatus" class="input w-40">
            <option value="">{{ __('Todos os status') }}</option>
            @foreach($statusLabels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <input wire:model.live="filterDate" type="date" class="input w-40" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Data/Hora') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Paciente') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Médico') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Sala') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Status') }}</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($appointments as $a)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $a->scheduled_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $a->scheduled_at->format('H:i') }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300">{{ $a->patient->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $a->doctor->user->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $a->room?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$a->status] ?? '' }}">
                                {{ __($statusLabels[$a->status] ?? $a->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $a)
                                    <button wire:click="openEdit('{{ $a->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $a)
                                    @if($confirmId === $a->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">{{ __('Confirmar?') }}</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">{{ __('Sim') }}</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">{{ __('Não') }}</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $a->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">{{ __('Nenhum agendamento encontrado.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($appointments->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $appointments->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? __('Editar agendamento') : __('Novo agendamento') }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Paciente') }}</label>
                        <select wire:model="formPatientId" class="input">
                            <option value="">— Selecione —</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('formPatientId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Médico') }}</label>
                        <select wire:model="formDoctorId" class="input">
                            <option value="">— Selecione —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->user->name }} — {{ $doc->specialty }}</option>
                            @endforeach
                        </select>
                        @error('formDoctorId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Data e hora') }}</label>
                            <input wire:model="formScheduledAt" type="datetime-local" class="input" />
                            @error('formScheduledAt')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Status') }}</label>
                            <select wire:model="formStatus" class="input">
                                @foreach($statusLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Sala') }}</label>
                        <select wire:model="formRoomId" class="input">
                            <option value="">— Nenhuma —</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Observações') }}</label>
                        <textarea wire:model="formNotes" rows="3" class="input resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">{{ __('Cancelar') }}</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>{{ __('Salvar') }}</span><span wire:loading>{{ __('Salvando...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
