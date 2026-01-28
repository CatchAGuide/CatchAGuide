@php
    // Hreflang for the same path on different domains.
    // We intentionally avoid query strings so alternates match canonical behavior.
    $path = request()->path();
    $path = $path === '/' ? '' : $path;
    $path = ltrim($path, '/');

    $enBase = rtrim(env('EN_APP_URL', 'https://www.catchaguide.com'), '/');
    $deBase = rtrim(env('DE_APP_URL', 'https://www.catchaguide.de'), '/');

    $enUrl = $enBase . ($path !== '' ? '/' . $path : '/');
    $deUrl = $deBase . ($path !== '' ? '/' . $path : '/');
@endphp

<link rel="alternate" hreflang="en" href="{{ $enUrl }}" />
<link rel="alternate" hreflang="de" href="{{ $deUrl }}" />
<link rel="alternate" hreflang="x-default" href="{{ $enUrl }}" />

