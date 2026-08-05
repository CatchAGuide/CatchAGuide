@php
    $articleUrl = route($blogPrefix.'.thread.show', [$featured->slug]);
    $categoryName = $featured->category ? getLocalizedValue($featured->category) : null;
    $title = $featured->title;
    $excerpt = \Illuminate\Support\Str::limit(strip_tags($featured->excerpt ?: ''), 180);
@endphp
<article class="cag-mag-featured">
    <a
        href="{{ $articleUrl }}"
        class="cag-mag-featured__link"
        data-magazine-analytics="magazine_featured_click"
        data-magazine-slug="{{ $featured->slug }}"
        @if($categoryName) data-magazine-category="{{ $categoryName }}" @endif
    >
        <div class="cag-mag-featured__media" style="background-image: url('{{ $featured->getThumbnailPath() }}');" role="img" aria-label="{{ $title }}"></div>
        <div class="cag-mag-featured__content">
            <span class="cag-mag-featured__badge">{{ __('magazine.featured_badge') }}</span>
            @if($categoryName)
                <span class="cag-mag-featured__category">{{ $categoryName }}</span>
            @endif
            <h2 class="cag-mag-featured__title">{{ $title }}</h2>
            @if($excerpt !== '')
                <p class="cag-mag-featured__excerpt">{{ $excerpt }}</p>
            @endif
            <div class="cag-mag-featured__meta">
                <span>{{ __('magazine.by_author', ['author' => $featured->author]) }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ __('magazine.min_read', ['count' => $featured->estimatedReadingMinutes()]) }}</span>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $featured->created_at->toDateString() }}">{{ $featured->created_at->format('d.m.Y') }}</time>
            </div>
            <span class="cag-mag-featured__cta">{{ __('magazine.read_article') }}</span>
        </div>
    </a>
</article>
