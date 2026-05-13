<?php

use App\Actions\Clinica\Payments\CreatePaymentAction;
use App\Actions\Clinica\Payments\DeletePaymentAction;
use App\Actions\Clinica\Payments\UpdatePaymentAction;
use App\Models\Appointment;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string  $filterStatus = '';
    public string  $filterMonth  = '';
    public bool    $showForm     = false;
    public ?string $editingId    = null;
    public ?string $confirmId    = null;

    public string  $formAppointmentId = '';
    public string  $formAmount        = '';
    public string  $formMethod        = 'pix';
    public string  $formStatus        = 'pending';

    private static array $METHOD_LABELS = [
        'cash'      => 'Dinheiro',
        'card'      => 'Cartão',
        'pix'       => 'PIX',
        'insurance' => 'Convênio',
    ];

    private static array $STATUS_LABELS = [
        'pending'  => 'Pendente',
        'paid'     => 'Pago',
        'refunded' => 'Estornado',
    ];

    private static array $STATUS_COLORS = [
        'pending'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'paid'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'refunded' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    ];

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterMonth(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formAppointmentId', 'formAmount', 'editingId', 'confirmId']);
        $this->formMethod = 'pix';
        $this->formStatus = 'pending';
        $this->showForm   = true;
    }

    public function openEdit(string $id): void
    {
        $pay = Payment::findOrFail($id);
        $this->editingId        = $id;
        $this->formAppointmentId = $pay->appointment_id;
        $this->formAmount       = number_format($pay->amount, 2, ',', '');
        $this->formMethod       = $pay->method;
        $this->formStatus       = $pay->status;
        $this->showForm         = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->validate([
            'formAppointmentId' => ['required', 'exists:appointments,id'],
            'formAmount'        => ['required', 'numeric', 'min:0.01'],
            'formMethod'        => ['required', 'in:cash,card,pix,insurance'],
            'formStatus'        => ['required', 'in:pending,paid,refunded'],
        ]);

        $amount = (float) str_replace(',', '.', $this->formAmount);

        if ($this->editingId) {
            $pay = Payment::findOrFail($this->editingId);
            app(UpdatePaymentAction::class)->handle($pay, $amount, $this->formMethod, $this->formStatus);
            $msg = 'Pagamento atualizado.';
        } else {
            app(CreatePaymentAction::class)->handle($this->formAppointmentId, $amount, $this->formMethod, $this->formStatus, null);
            $msg = 'Pagamento registrado.';
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $pay = Payment::findOrFail($this->confirmId);
        try {
            app(DeletePaymentAction::class)->handle($pay);
            session()->flash('success', 'Pagamento excluído.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->errors()['payment'][0]);
        }
        $this->confirmId = null;
    }

    public function with(): array
    {
        $methodLabels = self::$METHOD_LABELS;
        $statusLabels = self::$STATUS_LABELS;
        $statusColors = self::$STATUS_COLORS;

        $payments = Payment::with(['appointment.patient', 'appointment.doctor.user'])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterMonth, function ($q) {
                [$y, $m] = explode('-', $this->filterMonth);
                $q->whereYear('created_at', $y)->whereMonth('created_at', $m);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $appointments = Appointment::with(['patient', 'doctor.user'])
            ->whereDoesntHave('payment')
            ->orWhere('id', $this->formAppointmentId)
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return compact('payments', 'appointments', 'methodLabels', 'statusLabels', 'statusColors');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Pagamentos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Controle de pagamentos de consultas.</p>
        </div>
        @can('create', \App\Models\Payment::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Novo pagamento
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
        <select wire:model.live="filterStatus" class="input w-40">
            <option value="">Todos os status</option>
            @foreach($statusLabels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <input wire:model.live="filterMonth" type="month" class="input w-40" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Paciente</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Médico</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Método</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($payments as $pay)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-slate-800 dark:text-slate-100">{{ $pay->appointment->patient->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $pay->appointment->doctor->user->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $methodLabels[$pay->method] ?? $pay->method }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$pay->status] ?? '' }}">
                                {{ $statusLabels[$pay->status] ?? $pay->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-medium text-emerald-600 dark:text-emerald-400">
                            R$ {{ number_format($pay->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $pay)
                                    <button wire:click="openEdit('{{ $pay->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $pay)
                                    @if($confirmId === $pay->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">Confirmar?</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $pay->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">Nenhum pagamento encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($payments->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $payments->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar pagamento' : 'Novo pagamento' }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Agendamento</label>
                        <select wire:model="formAppointmentId" class="input" {{ $editingId ? 'disabled' : '' }}>
                            <option value="">— Selecione —</option>
                            @foreach($appointments as $ap)
                                <option value="{{ $ap->id }}">{{ $ap->patient->name }} — {{ $ap->scheduled_at->format('d/m/Y H:i') }}</option>
                            @endforeach
                        </select>
                        @error('formAppointmentId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Valor (R$)</label>
                        <input wire:model="formAmount" type="text" placeholder="0,00" class="input" />
                        @error('formAmount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Método</label>
                            <select wire:model="formMethod" class="input">
                                @foreach($methodLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
                            <select wire:model="formStatus" class="input">
                                @foreach($statusLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>Salvar</span><span wire:loading>Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
