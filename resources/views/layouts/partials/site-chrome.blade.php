{{--
  Shared public chrome: auth modals, account sheet, and either the inner-page
  slate header or a solid bar (checkout). Homepage / catalogs own their overlay nav.
--}}
@php
    use App\Support\SitePrimaryNav;

    $isHomepage = SitePrimaryNav::isHomepage();
    $includeNav = $includeNav ?? SitePrimaryNav::usesLayoutNav();
    $includeVacationOverlay = $includeVacationOverlay ?? SitePrimaryNav::usesVacationLoadingOverlay();
@endphp
@unless($isHomepage)
    @include('layouts.modal.loginModal')
    @include('layouts.modal.registerModal')
    @include('layouts.modal.guideApplicationModal')
    @include('layouts.partials.site-mobile-menu')

    @if($includeNav)
        @if(SitePrimaryNav::usesLayoutPageHeader())
            @include('layouts.partials.site-page-header')
        @else
            @include('layouts.partials.site-nav', [
                'overlay' => false,
                'idPrefix' => $idPrefix ?? 'site',
            ])
        @endif
    @endif

    @if($includeVacationOverlay)
        <div id="vacation-page-loading-overlay" class="vacation-page-loading-overlay" hidden aria-live="polite" aria-busy="true">
            <div class="vacation-page-loading-overlay__panel" role="status">
                <div class="spinner-border text-danger" aria-hidden="true"></div>
                <span>{{ __('checkout.loading') }}</span>
            </div>
        </div>
    @endif
@endunless
