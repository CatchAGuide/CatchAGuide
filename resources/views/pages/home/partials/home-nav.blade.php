{{-- Legacy homepage alias — prefers layouts.partials.site-nav. --}}
@include('layouts.partials.site-nav', [
    'overlay' => true,
    'idPrefix' => 'home',
])
