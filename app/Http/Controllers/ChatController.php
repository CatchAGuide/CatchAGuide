<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::all();
        $unreadmessages = ChatMessage::where('user_id', \Auth::user()->id)->where('is_read', 0)->get();
        foreach($unreadmessages as $messages) {
            $messages->is_read = 1;
            $messages->save();
        }
        return view('pages.chatsystem.index',compact('users'));
    }

    public function createChat(User $user)
    {
        $chat = Chat::where('user_id', $user->id)->where('user_two_id', auth()->id())->first();
        $chat_two = Chat::where('user_id', auth()->id())->where('user_two_id', $user->id)->first();

        if(!$chat && !$chat_two) {
            Chat::create([
                'user_id' => auth()->id(),
                'user_two_id' => $user->id
            ]);
        }

        return redirect()->route('chat');
    }
}
