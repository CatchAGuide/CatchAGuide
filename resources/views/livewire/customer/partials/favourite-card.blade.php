{{-- A saved tour. $fav from CustomerFavouriteService; $removable shows the Remove button. --}}
<article class="ga-fav" wire:key="fav-{{ $fav['id'] }}">
    <a href="{{ $fav['url'] }}" class="ga-fav__media">
        <img src="{{ $fav['image'] }}" alt="" loading="lazy" width="320" height="200">
    </a>
    <div class="ga-fav__body">
        <h3 class="ga-fav__title"><a href="{{ $fav['url'] }}" class="ga-plain-link">{{ $fav['title'] }}</a></h3>
        <p class="ga-fav__meta">
            @if ($fav['location'] !== '')<i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $fav['location'] }}@endif
            @if ($fav['duration']) · {{ $fav['duration'] }}@endif
        </p>
        <div class="ga-fav__foot">
            @if ($fav['price_label'])
                <span class="ga-fav__price">{{ __('guide_area.customer.favourites.from', ['price' => $fav['price_label']]) }}</span>
            @endif
            @if ($removable)
                <button type="button" class="ga-btn ga-btn--ghost ga-btn--sm" wire:click="remove({{ $fav['id'] }})" wire:loading.attr="disabled" aria-label="{{ __('guide_area.customer.favourites.remove_label', ['tour' => $fav['title']]) }}">
                    <i class="fas fa-heart-broken" aria-hidden="true"></i> {{ __('guide_area.common.remove') }}
                </button>
            @endif
        </div>
    </div>
</article>
