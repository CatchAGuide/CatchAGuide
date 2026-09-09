@php
    $articleUrl = route($blogPrefix.'.thread.show', [$thread->slug]);
    $categoryName = $thread->category ? getLocalizedValue($thread->category) : null;
    $title = $thread->title;
    $excerpt = \Illuminate\Support\Str::limit(strip_tags($thread->excerpt ?: ''), 110);
    $readingMins = $thread->estimatedReadingMinutes();
@endphp
<article class="cag-mag-card">
    <a
        href="{{ $articleUrl }}"
        class="cag-mag-card__link"
        data-magazine-analytics="magazine_article_click"
        data-magazine-slug="{{ $thread->slug }}"
        @if($categoryName) data-magazine-category="{{ $categoryName }}" @endif
    >
        <div class="cag-mag-card__media" style="background-image: url('{{ $thread->getThumbnailPath() }}');" role="img" aria-label="{{ $title }}"></div>
        <div class="cag-mag-card__body">
            <div class="cag-mag-card__meta">
                @if($categoryName)
                    <span class="cag-mag-card__category">{{ $categoryName }}</span>
                @endif
                <span class="cag-mag-card__read">{{ __('magazine.min_read', ['count' => $readingMins]) }}</span>
            </div>
            <h3 class="cag-mag-card__title">{{ $title }}</h3>
            @if($excerpt !== '')
                <p class="cag-mag-card__excerpt">{{ $excerpt }}</p>
            @endif
            <div class="cag-mag-card__footer">
                <span class="cag-mag-card__author">{{ __('magazine.by_author', ['author' => $thread->author]) }}</span>
                <time datetime="{{ $thread->created_at->toDateString() }}">{{ $thread->created_at->format('d.m.Y') }}</time>
            </div>
        </div>
    </a>
</article>
