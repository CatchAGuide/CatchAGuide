{{-- Pushes one schema.org block into the layout <head> (@stack('structured_data')).
     Usage: @include('components.seo.json-ld', ['data' => $array]) — a null/empty $data renders nothing. --}}
@if(! empty($data))
@push('structured_data')
    <script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@endif
