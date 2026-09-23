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
      $describeGuidingsFromTitle — /guidings* description falls back to the title (app.blade)
      $metaFallbacks — emit "{app name} - …" description/keywords when the view sets none
--}}
@inject('paginationSeo', 'App\Services\Seo\PaginationSeo')
@inject('pageAttributeLookup', 'App\Services\Seo\PageAttributeLookup')
@php
    $appName = e(config('app.name'));
    $viewTitle = trim($__env->yieldContent('title', 'Bitte Title setzen'));
    $pageSuffix = e($paginationSeo->titleSuffix(request()));
    $withAppName = fn (string $title, bool $append) => $title.$pageSuffix.($append ? ' - '.$appName : '');

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

        if ($onGuidings && ($describeGuidingsFromTitle ?? false)) {
            $metaDescription = $appName.' - '.$viewTitle;
        } elseif ($metaFallbacks ?? false) {
            $metaDescription = $appName.' - '.trim($__env->yieldContent('description'));
            $metaKeywords = $appName.' - '.trim($__env->yieldContent('keywords'));
        }
    }
@endphp
<title>{!! $metaTitle !!}</title>
@if(filled($metaDescription))
    <meta name="description" content="{!! $metaDescription !!}">
@endif
@if(filled($metaKeywords))
    <meta name="keywords" content="{!! $metaKeywords !!}">
@endif
