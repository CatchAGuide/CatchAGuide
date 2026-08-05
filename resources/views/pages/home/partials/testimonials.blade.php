@if(($testimonials ?? collect())->isNotEmpty())
<section id="cag-home-reviews" class="cag-home-section cag-home-reviews">
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-home-section__header--center">
            <h2 class="cag-home-section__title">{{ __('homepage.reviews_title') }}</h2>
        </div>

        <div class="cag-home-reviews__viewport" data-reviews-rail>
            <div class="cag-home-reviews__rail" role="list">
                @foreach([false, true] as $isClone)
                    @foreach($testimonials as $item)
                        <blockquote
                            class="cag-home-reviews__card"
                            role="listitem"
                            @if($isClone) aria-hidden="true" @endif
                        >
                            <div class="cag-home-reviews__header">
                                <div class="cag-home-reviews__avatar" aria-hidden="true">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item['author'] ?? 'A', 0, 1)) }}
                                </div>
                                <div class="cag-home-reviews__meta">
                                    <cite class="cag-home-reviews__author">{{ $item['author'] }}</cite>
                                    @if(!empty($item['date']))
                                        <span class="cag-home-reviews__date">{{ $item['date'] }}</span>
                                    @endif
                                </div>
                                <div class="cag-home-reviews__score" aria-label="{{ $item['score'] }} / 10">
                                    <span class="cag-home-reviews__score-val">{{ number_format((float) $item['score'], 1) }}</span>
                                    <span class="cag-home-reviews__score-den">/10</span>
                                </div>
                            </div>
                            <p class="cag-home-reviews__quote">“{{ $item['quote'] }}”</p>
                            @if(!empty($item['tour_title']))
                                @if(!empty($item['tour_url']))
                                    <a
                                        href="{{ $item['tour_url'] }}"
                                        class="cag-home-reviews__tour"
                                        @if($isClone) tabindex="-1" @endif
                                    >{{ $item['tour_title'] }}</a>
                                @else
                                    <p class="cag-home-reviews__tour">{{ $item['tour_title'] }}</p>
                                @endif
                            @endif
                        </blockquote>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
