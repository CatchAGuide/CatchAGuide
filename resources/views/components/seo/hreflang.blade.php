@php
    // Hreflang for the same logical page on .com (en) and .de (de).
    // Magazine prefixes differ; LocalePathMapper keeps alternates correct.
    $path = request()->path();
    $path = $path === '/' ? '' : ltrim($path, '/');
    $currentLang = app()->getLocale();

    $mapper = app(\App\Services\Seo\LocalePathMapper::class);
    $enBase = rtrim(config('cag.en_app_url'), '/');
    $deBase = rtrim(config('cag.de_app_url'), '/');

    $enUrl = $mapper->alternateUrl($enBase, $path, $currentLang, 'en');
    $deUrl = $mapper->alternateUrl($deBase, $path, $currentLang, 'de');
@endphp

<link rel="alternate" hreflang="en" href="{{ $enUrl }}" />
<link rel="alternate" hreflang="de" href="{{ $deUrl }}" />
<link rel="alternate" hreflang="x-default" href="{{ $enUrl }}" />
