<?php

use App\Actions\Clinica\Events\CreateEventAction;
use App\Actions\Clinica\Events\DeleteEventAction;
use App\Actions\Clinica\Events\UpdateEventAction;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string  $search    = '';
    public bool    $showForm  = false;
    public ?string $editingId = null;
    public ?string $confirmId = null;

    public string  $formTitle       = '';
    public string  $formDescription = '';
    public string  $formStartAt     = '';
    public string  $formEndAt       = '';
    public string  $formColor       = '#3B82F6';
    public bool    $formIsPublic    = false;

    private static array $COLORS = [
        '#3B82F6' => 'Azul',
        '#10B981' => 'Verde',
        '#F59E0B' => 'Amarelo',
        '#EF4444' => 'Vermelho',
        '#8B5CF6' => 'Roxo',
        '#EC4899' => 'Rosa',
        '#6B7280' => 'Cinza',
    ];

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['formTitle', 'formDescription', 'editingId', 'confirmId']);
        $this->formStartAt  = now()->format('Y-m-d\TH:i');
        $this->formEndAt    = now()->addHour()->format('Y-m-d\TH:i');
        $this->formColor    = '#3B82F6';
        $this->formIsPublic = false;
        $this->showForm     = true;
    }

    public function openEdit(string $id): void
    {
        $ev = Event::findOrFail($id);
        $this->editingId       = $id;
        $this->formTitle       = $ev->title;
        $this->formDescription = $ev->description ?? '';
        $this->formStartAt     = $ev->start_at->format('Y-m-d\TH:i');
        $this->formEndAt       = $ev->end_at->format('Y-m-d\TH:i');
        $this->formColor       = $ev->color;
        $this->formIsPublic    = $ev->is_public;
        $this->showForm        = true;
    }

    public function closeForm(): void { $this->showForm = false; }

    public function save(): void
    {
        $this->validate([
            'formTitle'       => ['required', 'string', 'max:150'],
            'formDescription' => ['nullable', 'string', 'max:1000'],
            'formStartAt'     => ['required', 'date'],
            'formEndAt'       => ['required', 'date', 'after:formStartAt'],
            'formColor'       => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'formEndAt.after' => 'O término deve ser após o início.',
        ]);

        if ($this->editingId) {
            $ev = Event::findOrFail($this->editingId);
            app(UpdateEventAction::class)->handle($ev, $this->formTitle, $this->formDescription ?: null, $this->formStartAt, $this->formEndAt, $this->formColor, $this->formIsPublic);
            $msg = "Evento \"{$ev->title}\" atualizado.";
        } else {
            $ev = app(CreateEventAction::class)->handle(Auth::user(), $this->formTitle, $this->formDescription ?: null, $this->formStartAt, $this->formEndAt, $this->formColor, $this->formIsPublic);
            $msg = "Evento \"{$ev->title}\" criado.";
        }

        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function confirmDelete(string $id): void { $this->confirmId = $id; }

    public function delete(): void
    {
        $ev = Event::findOrFail($this->confirmId);
        app(DeleteEventAction::class)->handle($ev);
        session()->flash('success', "Evento \"{$ev->title}\" excluído.");
        $this->confirmId = null;
    }

    public function with(): array
    {
        $colors = self::$COLORS;

        $events = Event::with('user')
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy('start_at', 'desc')
            ->paginate(15);

        return compact('events', 'colors');
    }
}; ?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Eventos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Agenda de eventos e compromissos.</p>
        </div>
        @can('create', \App\Models\Event::class)
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />Novo evento
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 p-3 rounded-xl text-sm bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />{{ session('success') }}
        </div>
    @endif

    <div class="card mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar evento..." class="input" />
    </div>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Evento</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Início</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Término</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Criado por</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Visib.</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($events as $ev)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $ev->color }}"></span>
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $ev->title }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $ev->start_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $ev->end_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $ev->user->name }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $ev->is_public ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ $ev->is_public ? 'Público' : 'Privado' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                @can('update', $ev)
                                    <button wire:click="openEdit('{{ $ev->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('delete', $ev)
                                    @if($confirmId === $ev->id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500">Confirmar?</span>
                                            <button wire:click="delete" class="text-xs text-red-600 hover:underline font-medium">Sim</button>
                                            <button wire:click="$set('confirmId', null)" class="text-xs text-slate-500 hover:underline">Não</button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete('{{ $ev->id }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">Nenhum evento encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($events->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700">{{ $events->links() }}</div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.closeForm()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editingId ? 'Editar evento' : 'Novo evento' }}
                    </h2>
                    <button wire:click="closeForm" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-x-mark class="w-4 h-4 text-slate-500" />
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Título</label>
                        <input wire:model="formTitle" type="text" class="input" />
                        @error('formTitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Descrição</label>
                        <textarea wire:model="formDescription" rows="2" class="input resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Início</label>
                            <input wire:model="formStartAt" type="datetime-local" class="input" />
                            @error('formStartAt')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Término</label>
                            <input wire:model="formEndAt" type="datetime-local" class="input" />
                            @error('formEndAt')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Cor</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($colors as $hex => $name)
                                <label class="cursor-pointer" title="{{ $name }}">
                                    <input type="radio" wire:model="formColor" value="{{ $hex }}" class="sr-only" />
                                    <span class="block w-7 h-7 rounded-full border-2 transition-all
                                        {{ $formColor === $hex ? 'border-slate-900 dark:border-white scale-110' : 'border-transparent' }}"
                                          style="background-color: {{ $hex }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="formIsPublic" class="w-4 h-4 rounded text-primary-600" />
                        <span class="text-sm text-slate-700 dark:text-slate-300">Visível para todos</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <button wire:click="closeForm" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary px-4 py-2 text-sm">
                        <span wire:loading.remove>Salvar</span><span wire:loading>Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
