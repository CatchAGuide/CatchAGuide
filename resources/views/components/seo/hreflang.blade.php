@php
    // Hreflang for the same logical page on .com (en) and .de (de).
    // Magazine prefixes differ; LocalePathMapper keeps alternates correct.
    //
    // Magazine articles and root-level guide articles are written per language with unrelated
    // slugs, so the same path on the other domain doesn't exist — declaring it sent Google to
    // 302s/404s (Search Console "Page with redirect"/"Not found"). Those pages get no alternates,
    // matching their sitemap entries (localized: false).
    $hasCounterpart = ! request()->routeIs('blog.thread.show', 'blogde.thread.show', 'category.thread');

    if ($hasCounterpart) {
        $path = request()->path();
        $path = $path === '/' ? '' : ltrim($path, '/');
        $currentLang = app()->getLocale();

        $mapper = app(\App\Services\Seo\LocalePathMapper::class);
        $enBase = rtrim(config('cag.en_app_url'), '/');
        $deBase = rtrim(config('cag.de_app_url'), '/');

        $enUrl = $mapper->alternateUrl($enBase, $path, $currentLang, 'en');
        $deUrl = $mapper->alternateUrl($deBase, $path, $currentLang, 'de');
    }
@endphp

@if($hasCounterpart)
<link rel="alternate" hreflang="en" href="{{ $enUrl }}" />
<link rel="alternate" hreflang="de" href="{{ $deUrl }}" />
<link rel="alternate" hreflang="x-default" href="{{ $enUrl }}" />
@endif
