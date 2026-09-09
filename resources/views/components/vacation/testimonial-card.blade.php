@props(['review'])

@php
    $filledStars = (int) round(((float) ($review['score'] ?? 0)) / 2);
@endphp

<article class="vacation-testimonial-card">
    <div class="vacation-testimonial-card__stars" aria-hidden="true">
        @for($i = 1; $i <= 5; $i++)
            <i class="fas fa-star {{ $i <= $filledStars ? 'is-filled' : '' }}"></i>
        @endfor
    </div>

    <p class="vacation-testimonial-card__quote">&ldquo;{{ $review['quote'] }}&rdquo;</p>

    <div class="vacation-testimonial-card__author">
        <span class="vacation-testimonial-card__avatar" aria-hidden="true"></span>
        <div>
            <div class="vacation-testimonial-card__name">{{ $review['author'] }}</div>
            @if(!empty($review['date']))
                <div class="vacation-testimonial-card__date">{{ $review['date'] }}</div>
            @endif
        </div>
    </div>

    @if(!empty($review['listing_title']))
        <div class="vacation-testimonial-card__tour">
            @if(!empty($review['listing_url']))
                <a href="{{ $review['listing_url'] }}">{{ $review['listing_title'] }}</a>
            @else
                {{ $review['listing_title'] }}
            @endif
        </div>
    @endif
</article>
