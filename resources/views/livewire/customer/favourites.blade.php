<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.customer.favourites.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.customer.favourites.subtitle') }}</p>
        </div>
    </header>

    @if ($items->isEmpty())
        <div class="ga-empty">
            <i class="far fa-heart" aria-hidden="true"></i>
            <p>{{ __('guide_area.customer.favourites.empty') }}</p>
            <a href="{{ route('guidings.index') }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ __('guide_area.customer.overview.find_tour') }}</a>
        </div>
    @else
        <div class="ga-fav-grid">
            @foreach ($items as $fav)
                @include('livewire.customer.partials.favourite-card', ['fav' => $fav, 'removable' => true])
            @endforeach
        </div>

        {{ $items->links('livewire.guide.partials.pager') }}
    @endif
</div>
