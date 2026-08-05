@if(($targetSpecies ?? collect())->isNotEmpty())
<section class="cag-home-section cag-home-species">
    <div class="cag-home-container">
        <div class="cag-home-section__header">
            <h2 class="cag-home-section__title">{{ __('homepage.species_title') }}</h2>
            <a href="{{ route('category.types', ['type' => 'targets']) }}" class="cag-home-section__link">
                {{ __('homepage.species_view_all') }}
            </a>
        </div>
        <div class="cag-home-species__rail" role="list">
            @foreach($targetSpecies as $species)
                <a href="{{ $species['url'] }}" class="cag-home-species__item" role="listitem">
                    <span class="cag-home-species__avatar">
                        <img src="{{ $species['thumbnail'] }}" alt="{{ $species['name'] }}" loading="lazy" width="96" height="96">
                    </span>
                    <span class="cag-home-species__name">{{ $species['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
