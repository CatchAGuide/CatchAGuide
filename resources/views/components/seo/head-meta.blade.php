{{--
    The page's single <title> plus meta description/keywords. Precedence (unchanged from the
    per-layout blocks this replaces): admin page attributes (page_attributes) > the view's own
    sections > generic fallbacks. The old blocks looped over the attribute rows and printed a
    <title> per row, so a page with a title and a description row got two <title> tags. Paginated
    listings get a "Page N" suffix (PaginationSeo).

    Values from @section are already HTML-escaped by Blade; everything else is escaped here, and
    the assembled strings are printed raw — escaping section values again produced "&amp;amp;".

    Per-layout flags keep each layout's existing title format:
      $appNameOnAttributes / $appNameOnGuidings / $appNameElsewhere — append " - {app name}"

    Description/keywords: the view's own @section('description') / ('keywords'), as plain text,
    capped at 160 characters, without a brand prefix — and nothing when the view sets none (Google
    then writes its own snippet). The old blocks dropped the view's description entirely on
    layouts.app pages (guide articles, trips, camps) and elsewhere prefixed "{app name} - " to an
    untruncated paragraph.
--}}
@inject('paginationSeo', 'App\Services\Seo\PaginationSeo')
@inject('pageAttributeLookup', 'App\Services\Seo\PageAttributeLookup')
@php
    $appName = e(config('app.name'));
    $viewTitle = trim($__env->yieldContent('title', 'Bitte Title setzen'));
    $pageSuffix = e($paginationSeo->titleSuffix(request()));
    $withAppName = fn (string $title, bool $append) => $title.$pageSuffix.($append ? ' - '.$appName : '');
    // Section values arrive HTML-escaped; some CMS copy is stored escaped too ("k&amp;uuml;nstlich").
    // Decode until stable, strip tags, collapse whitespace, cap, re-escape once.
    $plainText = function (?string $escaped, int $limit): ?string {
        $text = (string) $escaped;
        for ($i = 0; $i < 3 && ($decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')) !== $text; $i++) {
            $text = $decoded;
        }
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');

        return $text === '' ? null : e(Str::limit($text, $limit, '…'));
    };

    $attributes = $pageAttributeLookup->forRequest(request());
    $metaDescription = null;
    $metaKeywords = null;

    if ($attributes->isNotEmpty()) {
        $titleAttribute = $attributes->firstWhere('meta_type', 'title');
        $metaTitle = $withAppName($titleAttribute ? e($titleAttribute->content) : $viewTitle, $appNameOnAttributes ?? false);
        $descriptionAttribute = $attributes->firstWhere('meta_type', 'description');
        $keywordsAttribute = $attributes->firstWhere('meta_type', 'keywords');
        $metaDescription = $descriptionAttribute ? e($descriptionAttribute->content) : null;
        $metaKeywords = $keywordsAttribute ? e($keywordsAttribute->content) : null;
    } elseif (request()->segment(1) === 'guidings' && trim($__env->yieldContent('title')) === '') {
        $metaTitle = 'Guidings'.$pageSuffix.' - '.$appName;
        $metaDescription = $appName.' Guidings';
    } else {
        $onGuidings = request()->segment(1) === 'guidings';
        $metaTitle = $withAppName($viewTitle, $onGuidings ? ($appNameOnGuidings ?? false) : ($appNameElsewhere ?? false));
        $metaDescription = $plainText($__env->yieldContent('description'), 160);
        $metaKeywords = $plainText($__env->yieldContent('keywords'), 255);
    }
@endphp
<title>{!! $metaTitle !!}</title>
@if(filled($metaDescription))
    <meta name="description" content="{!! $metaDescription !!}">
@endif
@if(filled($metaKeywords))
    <meta name="keywords" content="{!! $metaKeywords !!}">
@endif
