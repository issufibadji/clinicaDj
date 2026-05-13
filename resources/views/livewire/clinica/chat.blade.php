<?php

use App\Actions\Clinica\Chat\SendMessageAction;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?string $recipientId = null;
    public string  $message     = '';

    public function selectRecipient(string $userId): void
    {
        $this->recipientId = $userId;
        $this->markAsRead();
    }

    public function send(): void
    {
        $this->validate([
            'message'     => ['required', 'string', 'max:2000'],
            'recipientId' => ['required', 'exists:users,id'],
        ]);

        app(SendMessageAction::class)->handle(
            Auth::user(),
            User::findOrFail($this->recipientId),
            $this->message,
        );

        $this->message = '';
    }

    private function markAsRead(): void
    {
        if (! $this->recipientId) return;

        ChatMessage::where('from_user_id', $this->recipientId)
            ->where('to_user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function loadMessages(): void
    {
        // chamado pelo wire:poll — força re-render
    }

    public function with(): array
    {
        $myId = Auth::id();

        $contacts = User::where('id', '!=', $myId)
            ->whereHas('sentMessages', fn($q) => $q->where('to_user_id', $myId)
                ->orWhere('from_user_id', $myId))
            ->orWhereHas('receivedMessages', fn($q) => $q->where('from_user_id', $myId))
            ->withCount(['receivedMessages as unread_count' => fn($q) =>
                $q->where('from_user_id', $myId)->whereNull('read_at')])
            ->orderBy('name')
            ->get();

        // Se não há contatos, mostrar todos os usuários para iniciar conversa
        if ($contacts->isEmpty()) {
            $contacts = User::where('id', '!=', $myId)->orderBy('name')->get();
        }

        $messages = collect();
        $recipient = null;

        if ($this->recipientId) {
            $recipient = User::find($this->recipientId);
            $messages  = ChatMessage::where(function ($q) use ($myId) {
                    $q->where('from_user_id', $myId)->where('to_user_id', $this->recipientId);
                })
                ->orWhere(function ($q) use ($myId) {
                    $q->where('from_user_id', $this->recipientId)->where('to_user_id', $myId);
                })
                ->with('sender')
                ->orderBy('created_at')
                ->get();

            $this->markAsRead();
        }

        return compact('contacts', 'messages', 'recipient');
    }
}; ?>

<div wire:poll.3s="loadMessages" class="flex h-[calc(100vh-8rem)] gap-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">

    {{-- Sidebar de contatos --}}
    <div class="w-72 flex-shrink-0 border-r border-slate-200 dark:border-slate-700 flex flex-col">
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Mensagens</h2>
        </div>

        <div class="overflow-y-auto flex-1 divide-y divide-slate-100 dark:divide-slate-700/60">
            @forelse($contacts as $contact)
                <button wire:click="selectRecipient('{{ $contact->id }}')"
                        class="w-full flex items-center gap-3 px-4 py-3 text-left transition-colors
                               {{ $recipientId === $contact->id
                                   ? 'bg-primary-50 dark:bg-primary-900/20'
                                   : 'hover:bg-slate-50 dark:hover:bg-slate-700/40' }}">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ $contact->initials() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">{{ $contact->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $contact->email }}</p>
                    </div>
                    @if(isset($contact->unread_count) && $contact->unread_count > 0)
                        <span class="w-5 h-5 rounded-full bg-primary-600 text-white text-xs flex items-center justify-center flex-shrink-0">
                            {{ $contact->unread_count }}
                        </span>
                    @endif
                </button>
            @empty
                <div class="px-4 py-8 text-center text-xs text-slate-400">
                    Nenhum usuário disponível.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Área de mensagens --}}
    <div class="flex-1 flex flex-col min-w-0">
        @if(! $recipient)
            <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
                <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" />
                <p class="text-slate-400 dark:text-slate-500 text-sm">Selecione um contato para iniciar a conversa.</p>
            </div>
        @else
            {{-- Header do chat --}}
            <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ $recipient->initials() }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $recipient->name }}</p>
                    <p class="text-xs text-slate-400">{{ $recipient->email }}</p>
                </div>
            </div>

            {{-- Mensagens --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3"
                 x-data x-init="$el.scrollTop = $el.scrollHeight"
                 x-on:livewire:navigated.window="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">

                @forelse($messages as $msg)
                    @php $isMine = $msg->from_user_id === Auth::id(); @endphp
                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md">
                            <div class="px-3 py-2 rounded-2xl text-sm
                                {{ $isMine
                                    ? 'bg-primary-600 text-white rounded-br-sm'
                                    : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-100 rounded-bl-sm' }}">
                                {{ $msg->body }}
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5 {{ $isMine ? 'text-right' : 'text-left' }}">
                                {{ $msg->created_at->format('H:i') }}
                                @if($isMine && $msg->read_at)
                                    · lida
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full text-sm text-slate-400">
                        Nenhuma mensagem ainda. Diga olá!
                    </div>
                @endforelse
            </div>

            {{-- Input --}}
            @can('create', \App\Models\ChatMessage::class)
                <div class="flex items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex-shrink-0">
                    <input wire:model="message"
                           wire:keydown.enter="send"
                           type="text"
                           placeholder="Digite uma mensagem..."
                           class="input flex-1" />
                    <button wire:click="send" wire:loading.attr="disabled"
                            class="btn-primary px-4 py-2 text-sm flex-shrink-0">
                        <span wire:loading.remove wire:target="send">
                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                        </span>
                        <span wire:loading wire:target="send">...</span>
                    </button>
                </div>
            @endcan
        @endif
    </div>
</div>
