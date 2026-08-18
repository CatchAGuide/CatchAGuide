{{--
  Shared public chrome: auth modals, account sheet, and (unless the page owns an overlay nav) the solid site header.
  Homepage keeps its own overlay nav + modals + bottom bar.
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
        @include('layouts.partials.site-nav', [
            'overlay' => false,
            'idPrefix' => $idPrefix ?? 'site',
        ])
    @endif

    @if($includeVacationOverlay)
        <div id="vacation-page-loading-overlay" class="vacation-page-loading-overlay" hidden aria-live="polite" aria-busy="true">
            <div class="vacation-page-loading-overlay__panel" role="status">
                <div class="spinner-border text-danger" aria-hidden="true"></div>
                <span>{{ translate('Loading...') }}</span>
            </div>
        </div>
    @endif
@endunless
