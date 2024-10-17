<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use App\Models\Chat as ChatModel;

class Chat extends Component
{
    public $activeChat;
    public $messagesCount = 0;
    public $chats;
    public $message = '';

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];

    public function render(): Factory|View|Application
    {
        return view('livewire.chat', [
            'chats' => $this->chats,
            'active_chat' => $this->activeChat
        ]);
    }

    public function mount(): void
    {
        $this->chats = auth()->user()->chats();
        $this->activeChat = $this->chats?->first();
        if($this->activeChat) {
            $this->messagesCount = $this->activeChat->messages()->count();
        }
    }

    public function switchChat($chat_id): void
    {
        if($chat_id !== $this->activeChat->id) {
            $this->activeChat = ChatModel::find($chat_id);

            $this->emit('refreshComponent');
        }
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => ['required', 'string']
        ]);

        $this->activeChat->messages()->create([
            'user_id' => auth()->id(),
            'message' => $this->message
        ]);

        $this->activeChat->update(['last_message_at' => now()]);

        $this->message = '';
        $this->activeChat->refresh();
        $this->emit('reloadMessages');
    }

    public function refreshMessages()
    {
        if($this->activeChat) {
            if($this->activeChat->messages()->count() > $this->messagesCount) {
                $this->messagesCount = $this->activeChat->messages()->count();
                $this->emit('reloadMessages');
            }
        }
    }
}
