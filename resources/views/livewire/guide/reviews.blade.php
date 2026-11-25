<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.reviews.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.reviews.subtitle') }}</p>
        </div>
    </header>

    @if ($summary['count'] === 0)
        <div class="ga-empty">
            <i class="far fa-star" aria-hidden="true"></i>
            <p>{{ __('guide_area.reviews.empty') }}</p>
        </div>
    @else
        {{-- Summary (spec RV-01) --}}
        <section class="ga-score-card">
            <div class="ga-score-card__total">
                <div class="ga-score-ring" aria-label="{{ __('guide_area.reviews.out_of', ['score' => number_format($summary['overall'], 1)]) }}">
                    <span class="ga-score-ring__value">{{ number_format($summary['overall'], 1) }}</span>
                    <span class="ga-score-ring__max">{{ __('guide_area.reviews.of_ten') }}</span>
                </div>
                <span class="ga-score-card__label">{{ $summary['label'] }}</span>
                <span class="ga-score-card__count">{{ trans_choice('guide_area.reviews.verified', $summary['verified'], ['count' => $summary['verified']]) }}</span>
            </div>
            <div class="ga-score-card__bars">
                @foreach (['overall', 'guide', 'region'] as $key)
                    <div class="ga-score-bar">
                        <span class="ga-score-bar__name">{{ __('guide_area.reviews.scores.'.$key) }}</span>
                        <span class="ga-score-bar__segments" aria-hidden="true">
                            @for ($i = 1; $i <= 10; $i++)
                                <span class="ga-score-bar__segment"><span style="width: {{ max(0, min(100, ($summary[$key] - ($i - 1)) * 100)) }}%"></span></span>
                            @endfor
                        </span>
                        <span class="ga-score-bar__value">{{ number_format($summary[$key], 1) }}<small>/10</small></span>
                    </div>
                @endforeach
                <p class="ga-score-card__note"><i class="far fa-check-circle" aria-hidden="true"></i> <strong>{{ __('guide_area.reviews.real') }}</strong><br>{{ __('guide_area.reviews.real_text') }}</p>
            </div>
        </section>

        <h2 class="ga-section-head__title ga-reviews-title">{{ __('guide_area.reviews.all') }}</h2>
        <ul class="ga-review-grid">
            @foreach ($items as $review)
                <li class="ga-review" wire:key="review-{{ $review['id'] }}">
                    <div class="ga-review__head">
                        <span class="ga-avatar ga-avatar--xs" aria-hidden="true">{{ $review['initial'] }}</span>
                        <div class="ga-review__who">
                            <span class="ga-review__name">{{ $review['guest'] }}</span>
                            <span class="ga-review__date">{{ $review['date'] }}</span>
                        </div>
                        @if ($review['is_new'])
                            <span class="ga-new">{{ __('guide_area.reviews.new') }}</span>
                        @endif
                        <span class="ga-score-pill" title="{{ __('guide_area.reviews.scores.guide') }} {{ number_format($review['guide'], 1) }} · {{ __('guide_area.reviews.scores.region') }} {{ number_format($review['region'], 1) }}">{{ number_format($review['overall'], 1) }}<small>/10</small></span>
                    </div>
                    @if ($review['comment'] !== '')
                        <p class="ga-review__text">“{{ $review['comment'] }}”</p>
                    @endif
                    @if ($review['is_automatic'])
                        <span class="ga-badge">{{ __('guide_area.reviews.automatic') }}</span>
                    @endif
                    @if ($review['tour'] !== '')
                        <p class="ga-review__tour">{{ $review['tour'] }}</p>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="ga-list-foot">
            <span class="ga-muted">{{ __('guide_area.reviews.showing', ['from' => $page->firstItem(), 'to' => $page->lastItem(), 'total' => $page->total()]) }}</span>
            {{ $page->links('livewire.guide.partials.pager') }}
        </div>
    @endif
</div>
