@if(($targetSpecies ?? collect())->isNotEmpty())
<section class="cag-home-section cag-home-species">
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-home-species__header">
            <div class="cag-home-species__heading">
                <h2 class="cag-home-section__title">{{ __('homepage.species_title') }}</h2>
                <p class="cag-home-species__subtitle">{{ __('homepage.species_subtitle') }}</p>
            </div>
            <a href="{{ route('category.types', ['type' => 'targets']) }}" class="cag-home-section__link">
                {{ __('homepage.species_view_all') }}
            </a>
        </div>

        <div class="cag-home-species__grid" role="list">
            @foreach($targetSpecies as $species)
                <a href="{{ $species['url'] }}" class="cag-home-species__card" role="listitem">
                    <span class="cag-home-species__media">
                        <img
                            src="{{ $species['thumbnail'] }}"
                            alt="{{ $species['name'] }}"
                            loading="lazy"
                            width="140"
                            height="112"
                        >
                    </span>
                    <span class="cag-home-species__body">
                        <span class="cag-home-species__name">{{ $species['name'] }}</span>
                        <span class="cag-home-species__cta" aria-hidden="true">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
