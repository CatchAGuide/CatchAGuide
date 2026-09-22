{{--
    Similar guidings on the tour product page.
    Desktop: fishing-tours catalog list rows.
    Mobile: homepage-style horizontal offer-card carousel.
    Props: guidings (Collection<Guiding>), seeAllUrl (string), numGuests (?int), query (array)
--}}
@php
    use App\Presenters\Guiding\GuidingCardPresenter;

    $guidings = $guidings ?? collect();
    $seeAllUrl = $seeAllUrl ?? route('guidings.index');
    $numGuests = $numGuests ?? null;
    $query = $query ?? [];
    $carouselCards = $guidings->isNotEmpty()
        ? app(GuidingCardPresenter::class)->presentMany($guidings, $query)
        : collect();
@endphp
@if($guidings->isNotEmpty())
<section class="tour-details-two mb-5 p-0 guiding-similar-rail" data-guiding-similar-rail>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-sm-12">
                <h3 class="tour-details-two__title">@lang('guidings.Match_Guiding')</h3>

                {{-- Mobile: homepage offer-card carousel --}}
                <div class="guiding-similar-rail__carousel d-md-none cag-home-offers">
                    <div class="cag-home-offers__viewport" data-offer-rail="similar-guidings">
                        <div class="cag-home-offers__rail" role="list">
                            @foreach($carouselCards as $card)
                                @include('pages.home.partials.offer-card', [
                                    'card' => $card,
                                    'type' => 'tour',
                                    'revealIndex' => min($loop->index, 6),
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Desktop: catalog list rows --}}
                <div class="guiding-similar-rail__list d-none d-md-block">
                    <div class="tours-list__right">
                        <div class="tours-list__inner" id="similar-guidings-list">
                            @include('pages.guidings.partials.tour-list-rows', [
                                'guidings' => $guidings,
                                'collapseShowMore' => false,
                                'numGuests' => $numGuests,
                                'query' => $query,
                            ])
                        </div>
                    </div>
                </div>

                <div class="guiding-similar-rail__see-all">
                    <a href="{{ $seeAllUrl }}" class="btn btn-orange">{{ __('guidings.View_all_guidings') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
