{{--
    Similar guidings on the tour product page.
    Uses the fishing-tours listing card (gallery, specs, inclusions, book CTA).
    Props: guidings (Collection<Guiding>), seeAllUrl (string)
--}}
@php
    $guidings = $guidings ?? collect();
    $seeAllUrl = $seeAllUrl ?? route('guidings.index');
@endphp
@if($guidings->isNotEmpty())
<section class="tour-details-two mb-5 p-0 guiding-similar-rail" data-guiding-similar-rail>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-sm-12">
                <h3 class="tour-details-two__title">@lang('guidings.Match_Guiding')</h3>
                <div class="tours-list__right">
                    <div class="tours-list__inner" id="similar-guidings-list">
                        @include('pages.guidings.partials.guiding-card', [
                            'guidings' => $guidings,
                            'collapseShowMore' => false,
                        ])
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
