{{--
    Fishing-tours catalog list rows on the tour product page (same-guide + similar).
    Props: guidings (Collection<Guiding>), collapseShowMore (bool), numGuests (?int), query (array)
--}}
@php
    use App\Presenters\Offers\TourCardPresenter;

    $guidings = $guidings ?? collect();
    $collapseShowMore = $collapseShowMore ?? false;
    $numGuests = $numGuests ?? null;
    $query = $query ?? [];
    $presenter = app(TourCardPresenter::class);
@endphp
@foreach($guidings as $guiding)
    <div class="guiding-product-list-item {{ $collapseShowMore ? ($loop->index < 2 ? 'is-visible' : '') : 'is-visible' }}">
        <x-offers.list-row :card="$presenter->presentListRow($guiding, $numGuests, $query)" />
    </div>
@endforeach
