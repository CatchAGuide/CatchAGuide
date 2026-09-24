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

        // Page 2+ of a listing is its own self-canonical page (PaginationSeo), so its alternates
        // are page N of the other domain too — not page 1, which would contradict the canonical.
        // Follows the canonical: the layouts' default canonical keeps ?page=N, a page's own
        // canonical section may drop it.
        $page = app(\App\Services\Seo\PaginationSeo::class)->currentPage(request());
        $canonicalKeepsPage = ! $__env->hasSection('canonical')
            || str_contains($__env->yieldContent('canonical'), '?page='.$page);
        if ($page > 1 && $canonicalKeepsPage) {
            $enUrl .= '?page='.$page;
            $deUrl .= '?page='.$page;
        }
    }
@endphp

@if($hasCounterpart)
<link rel="alternate" hreflang="en" href="{{ $enUrl }}" />
<link rel="alternate" hreflang="de" href="{{ $deUrl }}" />
<link rel="alternate" hreflang="x-default" href="{{ $enUrl }}" />
@endif
