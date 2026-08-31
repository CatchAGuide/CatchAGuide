{{-- Preserve GET query values across a separate form submit. --}}
@foreach($query as $key => $value)
    @if(is_array($value))
        @foreach($value as $item)
            @if($item !== null && $item !== '')
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endif
        @endforeach
    @elseif($value !== null && $value !== '')
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endif
@endforeach
