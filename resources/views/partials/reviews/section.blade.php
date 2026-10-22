{{--
    Product page reviews for tours, trips and camps. $section comes from
    App\Presenters\Reviews\ReviewSectionPresenter (nothing renders when it is null).
    Desktop (≥768px): score panel, category bars and a guest comment rail.
    Mobile (≤767px): the tour mobile design, partials.reviews.mobile.
    Optional: $sectionId (default "reviews"), $railId (id of the desktop comment rail, which
    the tour page's "(N reviews)" links scroll to).
    Styles: resources/sass/components/_listing-reviews.scss and _tour-reviews-mobile.scss.
--}}
@if(!empty($section))
    @php
        $sectionId = $sectionId ?? 'reviews';
        $railId = $railId ?? $sectionId.'-comments';
        $reviewItems = $section['reviews'] ?? [];
        $reviewItemCount = count($reviewItems);
    @endphp
    <section class="listing-reviews" id="{{ $sectionId }}" data-reveal data-listing-reviews>
        <h2 class="listing-reviews__title">{{ __('guidings.Reviews') }}</h2>

        @include('partials.reviews.mobile', ['section' => $section])

        <div class="listing-reviews__desktop">
            <div class="listing-reviews__overview">
                <div class="listing-reviews__hero">
                    <div class="listing-reviews__ring">
                        <span class="listing-reviews__score">{{ one($section['score']) }}</span>
                        <span class="listing-reviews__outof">{{ __('guidings.over_10') }}</span>
                    </div>
                    <div class="listing-reviews__hero-meta">
                        <span class="listing-reviews__label">{{ $section['label'] }}</span>
                        <span class="listing-reviews__count">{{ trans_choice('guidings.reviews_based_on', $section['count'], ['count' => $section['count']]) }}</span>
                    </div>
                </div>

                <div class="listing-reviews__breakdown">
                    <ul class="listing-reviews__metrics">
                        @foreach($section['categories'] as $category)
                            @php $filledTicks = max(0, min(10, (int) round($category['value']))); @endphp
                            <li class="listing-reviews__metric">
                                <span class="listing-reviews__metric-label">{{ $category['label'] }}</span>
                                <span class="listing-reviews__ticks" aria-hidden="true">
                                    @for($tick = 1; $tick <= 10; $tick++)
                                        <i class="{{ $tick <= $filledTicks ? 'is-on' : '' }}"></i>
                                    @endfor
                                </span>
                                <span class="listing-reviews__metric-value">{{ one($category['value']) }}<small>/10</small></span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="listing-reviews__trust">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="m8 12 3 3 5-6"></path></svg>
                        <span>{{ __('guidings.Real_experiences') }}</span>
                    </div>
                    <p class="listing-reviews__disclaimer">{{ $section['disclaimer'] }}</p>
                </div>
            </div>

            @if($reviewItemCount > 0)
                <div class="listing-reviews__comments" data-listing-reviews-comments>
                    <div class="listing-reviews__comments-head">
                        <h3 class="listing-reviews__comments-title">{{ __('guidings.guest_comments') }}</h3>
                        @if($reviewItemCount > 1)
                            <div class="listing-reviews__arrows">
                                <button type="button" class="listing-reviews__arrow" data-rail-prev aria-label="{{ __('guidings.previous_comment') }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>
                                </button>
                                <button type="button" class="listing-reviews__arrow" data-rail-next aria-label="{{ __('guidings.next_comment') }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="listing-reviews__rail" id="{{ $railId }}" data-rail>
                        @foreach($reviewItems as $item)
                            <article class="listing-reviews__item" data-rail-item>
                                <div class="listing-reviews__person">
                                    <div class="listing-reviews__avatar" aria-hidden="true">{{ $item['initial'] }}</div>
                                    <div class="listing-reviews__person-meta">
                                        <span class="listing-reviews__name">{{ $item['name'] ?? '—' }}</span>
                                        <span class="listing-reviews__date">{{ $item['date'] ?? '—' }}</span>
                                    </div>
                                    <span class="listing-reviews__pill">{{ number_format($item['score'], 1) }}<small>/10</small></span>
                                </div>
                                @if(!empty($item['text']))
                                    <p class="listing-reviews__text" data-text>&ldquo;{{ $item['text'] }}&rdquo;</p>
                                    <button type="button" class="listing-reviews__more" data-more hidden
                                            data-label-more="{{ __('guidings.See_More') }}"
                                            data-label-less="{{ __('guidings.Show_Less') }}"
                                            aria-expanded="false">{{ __('guidings.See_More') }}</button>
                                @endif
                                @if(!empty($item['automatic']))
                                    <span class="listing-reviews__badge">{{ __('guidings.automatic_review_badge') }}</span>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <script>
    (function () {
        var root = document.querySelector('[data-listing-reviews-comments]');
        if (!root) { return; }

        var rail = root.querySelector('[data-rail]');
        var prev = root.querySelector('[data-rail-prev]');
        var next = root.querySelector('[data-rail-next]');

        // One card per click; wraps around at either end.
        function step(direction) {
            var item = rail.querySelector('[data-rail-item]');
            var gap = parseFloat(window.getComputedStyle(rail).columnGap) || 0;
            var width = item ? item.getBoundingClientRect().width + gap : rail.clientWidth;
            var max = rail.scrollWidth - rail.clientWidth;
            var target = rail.scrollLeft + direction * width;
            if (target > max + 4) { target = 0; }
            if (target < -4) { target = max; }
            rail.scrollTo({ left: target, behavior: 'smooth' });
        }

        if (prev) { prev.addEventListener('click', function () { step(-1); }); }
        if (next) { next.addEventListener('click', function () { step(1); }); }

        // "See more" only where the clamped comment is actually cut off.
        var items = Array.prototype.slice.call(rail.querySelectorAll('[data-rail-item]'));
        function measure() {
            items.forEach(function (item) {
                var text = item.querySelector('[data-text]');
                var more = item.querySelector('[data-more]');
                if (!text || !more || text.classList.contains('is-open')) { return; }
                more.hidden = text.scrollHeight <= text.clientHeight + 1;
            });
        }
        items.forEach(function (item) {
            var text = item.querySelector('[data-text]');
            var more = item.querySelector('[data-more]');
            if (!text || !more) { return; }
            more.addEventListener('click', function () {
                var open = text.classList.toggle('is-open');
                more.textContent = open ? more.dataset.labelLess : more.dataset.labelMore;
                more.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });

        measure();
        window.addEventListener('resize', measure);
        if (document.fonts && document.fonts.ready) { document.fonts.ready.then(measure); }
    })();
    </script>
@endif
