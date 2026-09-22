{{--
    Mobile-only tour reviews (≤767px): navy score band + metric ticks + swipeable guest comments.
    Desktop keeps the overview card and horizontal rail in newIndex.blade.php; see
    resources/sass/components/_tour-reviews-mobile.scss for the show/hide switch.
--}}
@php
    $mobileReviews = $reviews ?? collect();
    $mobileReviewCount = (int) ($reviews_count ?? $mobileReviews->count());
    $mobileSlideCount = $mobileReviews->count();
    $mobileMetrics = [
        ['label' => __('guidings.Overall'), 'value' => (float) $average_overall_score],
        ['label' => __('guidings.Guide'), 'value' => (float) $average_guide_score],
        ['label' => __('guidings.Region_Water'), 'value' => (float) $average_region_water_score],
    ];
    // Dots stop scaling past a handful of slides; fall back to a "1 / N" counter.
    $mobileUseDots = $mobileSlideCount <= 8;
@endphp

<section class="tour-reviews-m" id="ratings-container-mobile" data-tour-reviews-m aria-label="{{ __('guidings.Reviews') }}">
    <div class="tour-reviews-m__card">
        <div class="tour-reviews-m__hero">
            <div class="tour-reviews-m__score">
                <span class="tour-reviews-m__score-val">{{ one($average_grandtotal_score) }}</span>
                <span class="tour-reviews-m__score-den">/10</span>
            </div>
            <div class="tour-reviews-m__hero-meta">
                <div class="tour-reviews-m__label">{{ getRatingLabel($average_grandtotal_score) }}</div>
                <div class="tour-reviews-m__count">{{ trans_choice('guidings.reviews_based_on', $mobileReviewCount, ['count' => $mobileReviewCount]) }}</div>
            </div>
        </div>

        <ul class="tour-reviews-m__metrics">
            @foreach($mobileMetrics as $metric)
                @php $filledTicks = max(0, min(10, (int) round($metric['value']))); @endphp
                <li class="tour-reviews-m__metric">
                    <span class="tour-reviews-m__metric-label">{{ $metric['label'] }}</span>
                    <span class="tour-reviews-m__metric-bar">
                        <span class="tour-reviews-m__ticks" aria-hidden="true">
                            @for($tick = 1; $tick <= 10; $tick++)
                                <i class="{{ $tick <= $filledTicks ? 'is-on' : '' }}"></i>
                            @endfor
                        </span>
                        <span class="tour-reviews-m__metric-value">{{ one($metric['value']) }}<small>/10</small></span>
                    </span>
                </li>
            @endforeach
        </ul>

        <div class="tour-reviews-m__trust">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="m8.5 12.2 2.4 2.4 4.6-5"></path></svg>
            <span>{{ __('guidings.Real_experiences') }}</span>
        </div>
    </div>

    @if($mobileSlideCount > 0)
        <div class="tour-reviews-m__card tour-reviews-m__comments" data-reviews-slider>
            <div class="tour-reviews-m__comments-head">
                <h3 class="tour-reviews-m__comments-title">{{ __('guidings.guest_comments') }}</h3>
                @if($mobileSlideCount > 1)
                    <div class="tour-reviews-m__arrows">
                        <button type="button" class="tour-reviews-m__arrow" data-prev aria-label="{{ __('guidings.previous_comment') }}">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 6-6 6 6 6"></path></svg>
                        </button>
                        <button type="button" class="tour-reviews-m__arrow" data-next aria-label="{{ __('guidings.next_comment') }}">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 6 6 6-6 6"></path></svg>
                        </button>
                    </div>
                @endif
            </div>

            <div class="tour-reviews-m__track" data-track>
                @foreach($mobileReviews as $review)
                    @php
                        $reviewBooking = $review->booking;
                        $reviewDate = $reviewBooking
                            ? $reviewBooking->getFormattedBookingDate('M j, Y')
                            : ($review->created_at ? \Carbon\Carbon::parse($review->created_at)->format('M j, Y') : null);
                        $reviewerName = $review->user->firstname ?? null;
                        $reviewerInitial = $reviewerName
                            ? mb_strtoupper(mb_substr($reviewerName, 0, 1, 'UTF-8'), 'UTF-8')
                            : '?';
                    @endphp
                    <article class="tour-reviews-m__slide" data-slide>
                        <div class="tour-reviews-m__person">
                            <div class="tour-reviews-m__avatar" aria-hidden="true">{{ $reviewerInitial }}</div>
                            <div class="tour-reviews-m__person-meta">
                                <div class="tour-reviews-m__name">{{ $reviewerName ?? '—' }}</div>
                                <div class="tour-reviews-m__date">{{ $reviewDate ? strtoupper($reviewDate) : '—' }}</div>
                            </div>
                            <span class="tour-reviews-m__pill">{{ number_format($review->grandtotal_score, 1) }}<small>/10</small></span>
                        </div>
                        @if(filled($review->comment))
                            <p class="tour-reviews-m__text" data-text>&ldquo;{{ translate($review->comment) }}&rdquo;</p>
                            <button type="button" class="tour-reviews-m__more" data-more hidden
                                    data-label-more="{{ __('guidings.See_More') }}"
                                    data-label-less="{{ __('guidings.Show_Less') }}"
                                    aria-expanded="false">{{ __('guidings.See_More') }}</button>
                        @endif
                        @if($review->is_automatic)
                            <span class="tour-reviews-m__badge">{{ __('guidings.automatic_review_badge') }}</span>
                        @endif
                    </article>
                @endforeach
            </div>

            @if($mobileSlideCount > 1)
                <div class="tour-reviews-m__foot">
                    @if($mobileUseDots)
                        <div class="tour-reviews-m__dots">
                            @for($dot = 1; $dot <= $mobileSlideCount; $dot++)
                                <button type="button" class="tour-reviews-m__dot" data-dot aria-label="{{ __('guidings.show_comment', ['number' => $dot]) }}"></button>
                            @endfor
                        </div>
                    @else
                        <span class="tour-reviews-m__counter" data-counter data-total="{{ $mobileSlideCount }}">1 / {{ $mobileSlideCount }}</span>
                    @endif
                </div>
            @endif
        </div>
    @endif
</section>

<script>
(function () {
    var slider = document.querySelector('[data-tour-reviews-m] [data-reviews-slider]');
    if (!slider) { return; }

    var track = slider.querySelector('[data-track]');
    var slides = Array.prototype.slice.call(track.querySelectorAll('[data-slide]'));
    var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-dot]'));
    var counter = slider.querySelector('[data-counter]');
    var prev = slider.querySelector('[data-prev]');
    var next = slider.querySelector('[data-next]');
    var count = slides.length;
    var active = 0;

    function paint(index) {
        active = index;
        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === index);
        });
        if (counter) { counter.textContent = (index + 1) + ' / ' + count; }
    }

    function goTo(index) {
        var target = (index + count) % count;
        track.scrollTo({ left: target * track.clientWidth, behavior: 'smooth' });
        paint(target);
    }

    var ticking = false;
    track.addEventListener('scroll', function () {
        if (ticking) { return; }
        ticking = true;
        window.requestAnimationFrame(function () {
            ticking = false;
            var index = Math.round(track.scrollLeft / (track.clientWidth || 1));
            if (index !== active && index >= 0 && index < count) { paint(index); }
        });
    }, { passive: true });

    if (prev) { prev.addEventListener('click', function () { goTo(active - 1); }); }
    if (next) { next.addEventListener('click', function () { goTo(active + 1); }); }
    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { goTo(i); });
    });

    // "See more" only where the clamped comment is actually cut off.
    function measure() {
        slides.forEach(function (slide) {
            var text = slide.querySelector('[data-text]');
            var more = slide.querySelector('[data-more]');
            if (!text || !more || text.classList.contains('is-open')) { return; }
            more.hidden = text.scrollHeight <= text.clientHeight + 1;
        });
    }

    slides.forEach(function (slide) {
        var text = slide.querySelector('[data-text]');
        var more = slide.querySelector('[data-more]');
        if (!text || !more) { return; }
        more.addEventListener('click', function () {
            var open = text.classList.toggle('is-open');
            more.textContent = open ? more.dataset.labelLess : more.dataset.labelMore;
            more.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    var resizeTimer;
    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function () {
            track.scrollTo({ left: active * track.clientWidth });
            measure();
        }, 120);
    });

    paint(0);
    measure();
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(measure); }
})();
</script>
