@php
    $copy = 'guidings.voucher_landing.';
    $gallery = $landing->gallery();
    $reviews = $landing->reviews();
@endphp
@if($gallery !== [] || $reviews !== [])
<section class="vl-section vl-section--paper vl-section--quiet">
    <div class="vl-container vl-stack">
        <h2 class="vl-h2">{{ __($copy.'gallery_title') }}</h2>
        @if($gallery !== [])
            <div class="vl-gallery">
                @foreach($gallery as $image)
                    <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async">
                @endforeach
            </div>
        @endif
        @if($reviews !== [])
            <div class="vl-reviews" aria-label="{{ __($copy.'reviews_title') }}">
                @foreach($reviews as $review)
                    <figure class="vl-review">
                        <span class="vl-review__stars" aria-hidden="true">★★★★★</span>
                        <blockquote>„{{ $review['text'] }}“</blockquote>
                        <figcaption>
                            @if($review['name'] !== '')<strong>{{ $review['name'] }}</strong>@endif
                            @if($review['tour'] !== '')<span>{{ $review['tour'] }}</span>@endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
