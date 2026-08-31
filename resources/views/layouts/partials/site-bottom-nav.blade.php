@php
    use App\Support\SitePrimaryNav;
@endphp
@if(SitePrimaryNav::usesLayoutBottomNav())
    @include('pages.home.partials.mobile-bottom-nav')
@endif
