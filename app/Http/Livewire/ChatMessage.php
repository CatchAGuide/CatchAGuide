<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ChatMessage extends Component
{
    public $message;

    public function render()
    {
        return view('livewire.chat-message', [
            'message' => $this->message
        ]);
    }

    public function mount() {
        if(!$this->message->is_read && $this->message->user_id !== auth()->id()) {
            $this->message->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            $this->message->refresh();
        }
    }
}
