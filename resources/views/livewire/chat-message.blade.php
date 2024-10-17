<div>
    @if($message->user_id === auth()->id())
        <div class="outgoing_msg">
            <div class="sent_msg" style="{{$agent->ismobile() ? 'width: 100%;' : ''}} word-wrap: break-word;">
                <p>{{ $message->message }}</p>
                <span class="time_date">
                    @if($message->is_read)
                        <i class="fa fa-eye"></i>
                    @else
                        <i class="fa fa-eye-slash"></i>
                    @endif
                    {{ $message->created_at->diffForHumans(now()) }}</span></div>
        </div>
    @else
        <div class="incoming_msg">
            <div class="incoming_msg_img">

            </div>
            <div class="received_msg">
                <div class="received_withd_msg">
                    <p>{{ $message->message }}</p>
                    <span class="time_date">
                        {{ $message->created_at->diffForHumans(now()) }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
