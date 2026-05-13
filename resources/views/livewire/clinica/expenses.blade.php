<?php

use App\Actions\Clinica\Expenses\CreateExpenseAction;
use App\Actions\Clinica\Expenses\DeleteExpenseAction;
use App\Actions\Clinica\Expenses\UpdateExpenseAction;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string  $search        = '';
    public string  $filterMonth   = '';
    public bool    $showForm      = false;
    public ?string $editingId     = null;
    public ?string $confirmId     = null;

    public string $formDescription = '';
    public string $formAmount      = '';
    public string $formCategory    = '';
    public string $formDate        = '';

    private static array $CATEGORIES = [
        'Salários', 'Insumos', 'Equipamentos', 'Manutenção',
        'Aluguel', 'Energia', 'Água', 'Internet', 'Outros',
    ];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterMonth(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formDescription', 'formAmount', 'formCategory', 'editingId', 'confirmId']);
        $this->formDate = now()->format('Y-m-d');
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $exp = Expense::findOrFail($id);
        $this->editingId       = $id;
        $this->formDescription = $exp->description;
        $this->formAmount      = number_format($exp->amount, 2, ',', '');
        $this->formCategory    = $exp->category;
        $this->formDate        = $exp->date->format('Y-m-d');
        $this->showForm        = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->validate([
            'formDescription' => ['required', 'string', 'max:200'],
            'formAmount'      => ['required', 'numeric', 'min:0.01'],
            'formCategory'    => ['required', 'string', 'max:50'],
            'formDate'        => ['required', 'date'],
        ]);

        $amount = (float) str_replace(',', '.', $this->formAmount);

        if ($this->editingId) {
            $exp = Expense::findOrFail($this->editingId);
            app(UpdateExpenseAction::class)->handle($exp, $this->formDescription, $amount, $this->formCategory, $this->formDate);
            $msg = 'Despesa atualizada.';
        } else {
            app(CreateExpenseAction::class)->handle(Auth::user(), $this->formDescription, $amount, $this->formCategory, $this->formDate);
            $msg = 'Despesa registrada.';
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $exp = Expense::findOrFail($this->confirmId);
        app(DeleteExpenseAction::class)->handle($exp);
        session()->flash('success', 'Despesa excluída.');
        $this->confirmId = null;
    }

    public function with(): array
    {
        $categories = self::$CATEGORIES;

        $expenses = Expense::with('user')
            ->when($this->search, fn($q) => $q->where('description', 'like', "%{$this->search}%")
                ->orWhere('category', 'like', "%{$this->search}%"))
            ->when($this->filterMonth, fn($q) => $q->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$this->filterMonth]))
            ->orderBy('date', 'desc')
            ->paginate(15);

        $total = $expenses->sum('amount');

        return compact('expenses', 'categories', 'total');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Despesas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Controle de despesas operacionais.</p>
        </div>
        @can('create', \App\Models\Expense::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Nova despesa
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />{{ session('success') }}
        </div>
    @endif

    <div class="card mb-4 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar descrição ou categoria..." class="input flex-1 min-w-0" />
        <input wire:model.live="filterMonth" type="month" class="input w-40" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoria</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Data</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Registrado por</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-slate-800 dark:text-slate-100">{{ $exp->description }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $exp->date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $exp->user->name }}</td>
                        <td class="px-5 py-3.5 text-right font-medium text-red-600 dark:text-red-400">
                            R$ {{ number_format($exp->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $exp)
                                    <button wire:click="openEdit('{{ $exp->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $exp)
                                    @if($confirmId === $exp->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">Confirmar?</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $exp->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">Nenhuma despesa encontrada.</td></tr>
                @endforelse
            </tbody>
            @if($expenses->count() > 0)
                <tfoot>
                    <tr class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40">
                        <td colspan="4" class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Total (página)</td>
                        <td class="px-5 py-3 text-right font-bold text-red-600 dark:text-red-400">
                            R$ {{ number_format($total, 2, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
        @if($expenses->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $expenses->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar despesa' : 'Nova despesa' }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Descrição</label>
                        <input wire:model="formDescription" type="text" class="input" />
                        @error('formDescription')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Valor (R$)</label>
                            <input wire:model="formAmount" type="text" placeholder="0,00" class="input" />
                            @error('formAmount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data</label>
                            <input wire:model="formDate" type="date" class="input" />
                            @error('formDate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Categoria</label>
                        <select wire:model="formCategory" class="input">
                            <option value="">— Selecione —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('formCategory')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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
