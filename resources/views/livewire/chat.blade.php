<div>
    @if(count($chats) > 0)
        <div class="messaging">
            <div class="inbox_msg">
                {{--foreach--}}
                <div class="inbox_people">
                    <div class="inbox_chat">
                        @foreach($chats as $chat)
                            @php($last_message = $chat->last_message())
                            <div class="chat_list {{ ($chat->id === $active_chat->id) ? 'active_chat' : '' }}" wire:click="switchChat({{ $chat->id }})" wire:key="{{ $chat->id }}">
                                <div class="chat_people">
                                    <div class="chat_img">
                                        @if($chat->user_id !== auth()->id())
                                            @if($chat->user->profil_image)
                                                <img src="{{asset('images/'. $chat->user->profil_image )}}">
                                            @else
                                                <img src="https://ptetutorials.com/images/user-profile.png"
                                                     alt="sunil">
                                            @endif
                                        @else
                                            @if($chat->user_two->profil_image)
                                                <img src="{{asset('images/'. $chat->user_two->profil_image )}}">
                                            @else
                                                <img src="https://ptetutorials.com/images/user-profile.png"
                                                     alt="sunil">
                                            @endif
                                        @endif

                                    </div>
                                    <div class="chat_ib">
                                        <h5>
                                            @if($chat->user_id !== auth()->id())
                                                {{ $chat->user->full_name }}
                                            @else
                                                {{ $chat->user_two->full_name }}
                                            @endif
                                            <span class="chat_date">
                                            @if($last_message)
                                                {{ $last_message?->created_at?->format('d F') }}
                                            @endif
                                        </span>
                                        </h5>
                                        <p>
                                            @if($last_message)
                                                @if($last_message->user_id === auth()->id())
                                                    @if($last_message->is_read)
                                                        <i class="fa fa-eye"></i>
                                                    @else
                                                        <i class="fa fa-eye-slash"></i>
                                                    @endif
                                                @endif
                                                {{ !$agent->ismobile() ? $last_message->message : '' }}
                                            @else
                                                Keine Nachrichten vorhanden
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{--endforeach--}}
                <div class="mesgs">
                    <div class="msg_history" id="msg_history" wire:poll.visible="refreshMessages">
                        @if($this->activeChat)
                            @foreach($active_chat->messages->sortBy('created_at') as $message)
                                <livewire:chat-message wire:key="{{ $message->id }}" :message="$message"/>
                            @endforeach
                        @endif
                    </div>
                    @if($this->activeChat)
                        <div class="type_msg">
                            <div class="input_msg_write">
                                <input type="text" class="write_msg" placeholder=" @if($authUser->is_guide) {{ __('forms.msgYG') }} @else {{ __('forms.msgHere') }} @endif " wire:model="message" wire:keydown.enter="sendMessage"/>
                                <button class="msg_send_btn" type="button" wire:click="sendMessage"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>


        </div>
    @else
        <div class="alert alert-danger" role="alert">
            Du hast noch keine Nachrichten!
        </div>
    @endif
</div>

@push('js_push')
    <script>

        document.addEventListener('livewire:load', function () {
            Livewire.on('reloadMessages', () => {
                var objDiv = document.getElementById("msg_history");
                objDiv.scrollTop = objDiv.scrollHeight;
            })


            var objDiv = document.getElementById("msg_history");
            objDiv.scrollTop = objDiv.scrollHeight;
        });
    </script>
@endpush
