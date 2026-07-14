@php
    /** @var string $pillar 'camps'|'trips' */
    $pillar = $pillar ?? 'camps';
    $productTitle = $productTitle ?? '';
    $pillarIndexRoute = $pillar === 'trips' ? 'vacations.trips.index' : 'vacations.camps.index';
    $pillarLabelKey = $pillar === 'trips' ? 'vacations.pillar_trips_title' : 'vacations.pillar_camps_title';
@endphp
<section class="page-header vacation-offer-breadcrumb" aria-label="Breadcrumb">
    <div class="page-header__bottom breadcrumb-container">
        <div class="page-header__bottom-inner">
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                <li><a href="{{ route('vacations.index') }}">{{ __('vacations.hub_breadcrumb') }}</a></li>
                <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                <li><a href="{{ route($pillarIndexRoute) }}">{{ __($pillarLabelKey) }}</a></li>
                <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                <li class="active">{{ $productTitle }}</li>
            </ul>
        </div>
    </div>
</section>
