@props(['stats' => []])

@if(!empty($stats))
    <div class="d-flex flex-wrap gap-2">
        @foreach($stats as $stat)
            <div class="p-2 px-3 rounded border bg-light">
                <div class="small text-muted mb-1">{{ $stat['label'] }}</div>
                <div class="fw-bold">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </div>
@endif
