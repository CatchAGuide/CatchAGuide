@if(!empty($season))
<section class="cag-home-section cag-home-season">
    <div class="cag-home-container">
        <div class="cag-home-season__card">
            <div class="cag-home-season__copy">
                <span class="cag-home-season__badge">{{ __('homepage.season_badge') }}</span>
                <h2 class="cag-home-season__title">{{ $season['title'] }}</h2>
                <p class="cag-home-season__text">{{ $season['text'] }}</p>
                <a href="{{ $season['cta_url'] }}" class="cag-home-btn cag-home-btn--coral">
                    {{ __('homepage.season_cta', ['month' => $season['month']]) }}
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            @if(($season['species'] ?? collect())->isNotEmpty())
                <div class="cag-home-season__species">
                    @foreach($season['species'] as $species)
                        <a href="{{ $species['url'] }}" class="cag-home-season__species-card">
                            <img src="{{ $species['thumbnail'] }}" alt="{{ $species['name'] }}" loading="lazy">
                            <span>{{ $species['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endif
