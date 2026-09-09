{{--
  Shared public chrome: auth modals, account sheet, and either the inner-page
  slate header or a solid bar (checkout). Homepage / catalogs own their overlay nav.
--}}
@php
    use App\Support\SitePrimaryNav;

    $isHomepage = SitePrimaryNav::isHomepage();
    $includeNav = $includeNav ?? SitePrimaryNav::usesLayoutNav();
@endphp
@include('layouts.partials.page-loader')
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
@endunless
