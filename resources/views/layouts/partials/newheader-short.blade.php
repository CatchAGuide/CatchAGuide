@include('layouts.modal.loginModal')
@include('layouts.modal.registerModal')

@php
    $isCheckout = (request()->is('checkout') || request()->is('checkout/thank-you/*')) ? 1 : 0;
    $currentVacationCountry = $currentVacationCountry ?? null;
@endphp

<nav class="navbar-custom short-header {{ request()->is('/') ? 'with-bg' : '' }} {{ request()->is('guidings*') ? 'no-search' : '' }} {{ $isCheckout ? 'checkout-minimal' : '' }} {{ ($isCheckout) ? 'no-searchbar' : '' }} {{ $isVacation ? 'is-vacation' : '' }}">
    <div class="container">
        <!-- Top Row -->
        <div class="row align-items-center">
            <!-- Logo and Navigation -->
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('welcome') }}">
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Logo" style="height: auto;">
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="d-none d-md-flex align-items-center top-nav-items">
                    <a id="contact-header" href="{{ route('additional.contact') }}" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                    </a>
                    <div class="nav-link language-selector">
                        <form action="{{ route('language.switch') }}" method="POST" class="d-flex align-items-center" id="desktop-language-form">
                            @csrf
                            <i class="fas fa-map-signs me-2"></i>
                            <select name="language" class="selectpicker header-language-select" data-width="fit" onchange="handleLanguageSwitch(this, 'desktop-language-form')">
                                @foreach (config('app.locales') as $key => $locale)
                                    <option value="{{ $locale }}" 
                                            data-content='<span class="fi fi-{{$key}}"></span>' 
                                            {{ app()->getLocale() == $locale ? 'selected' : '' }}>
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    
                    @if(!$isCheckout)
                    <a href="#" class="nav-link become-guide-link" data-bs-toggle="modal" data-bs-target="#registerModal">
                        @lang('homepage.header-become-guide')
                    </a>
                    @endif
                    @auth
                        <div class="header-desktop-profile dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <img src="{{ asset('images/'. Auth::user()->profil_image) ?? asset('images/placeholder_guide.jpg') }}" 
                                     class="rounded-circle profile-image" 
                                     alt="Profile">
                                <span>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end z-index2">
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="fas fa-user me-2"></i> @lang('homepage.header-profile')
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> @lang('homepage.header-logout')
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="#" id="login-header" class="nav-link login-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                            @lang('homepage.header-login')
                        </a>
                        <a href="#" id="signup-header" class="btn btn-outline-light signup-btn" data-bs-toggle="modal" data-bs-target="#registerModal">
                            @lang('homepage.header-signup')
                        </a>
                    @endauth
                </div>

                <!-- Mobile Icons - Update alignment -->
                <div class="d-flex d-md-none align-items-center">
                    @auth
                        <a href="{{ route('profile.index') }}" class="me-3">
                            <img src="{{ asset('images/'. Auth::user()->profil_image) ?? asset('images/placeholder_guide.jpg') }}" 
                                 class="rounded-circle" 
                                 style="width: 32px; height: 32px;" 
                                 alt="Profile">
                        </a>
                    @else
                        <a href="#" id="login-header" class="text-white me-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="far fa-user-circle" style="font-size: 24px;"></i>
                        </a>
                    @endauth
                    <a href="#" class="text-white" data-bs-toggle="modal" data-bs-target="#mobileMenuModal">
                        <i class="fas fa-bars" style="font-size: 24px;"></i>
                    </a>
                </div>
            </div>
            @if (request()->is('fishing-magazine/*') || request()->is('angelmagazin/*'))
            <div class="header-contents container">
                <h1 class="h2 mt-3 mb-0 text-white">@yield('header_title')</h1>
            </div>
            @endif
            <!-- Categories Row - Mobile -->
            @if(!$isCheckout)
            <div class="col-12 d-md-none mt-2">
                <div class="d-flex categories-mobile">
                    <a href="{{ route('guidings.index') }}" 
                       class="me-4 text-white text-decoration-none {{ request()->is('guidings*') ? 'active' : '' }}">
                        <i class="fas fa-fish me-2"></i>@lang('homepage.filter-fishing-near-me')
                    </a>
                    <a href="{{ route('vacations.index') }}" 
                       class="me-4 text-white text-decoration-none {{ request()->is('vacations*') ? 'active' : '' }}">
                        <i class="fas fa-map-signs me-2"></i>@lang('homepage.header-vacations')
                    </a>
                    <a href="{{ route($blogPrefix.'.index') }}" 
                       class="me-4 text-white text-decoration-none {{ request()->is('angelmagazin*') ? 'active' : '' }}">
                        <i class="fas fa-book-open me-2"></i>@lang('homepage.filter-magazine')
                    </a>
                </div>
            </div>
            @endif

            @php
                $countries = \App\Models\Destination::where('type', 'vacations')->where('language',app()->getLocale())->pluck('name');
            @endphp

            @php
                // Collect all non-empty request filters (excluding internal/mobile helper param)
                $activeFilters = collect(request()->except(['ismobile']))
                    ->filter(function ($value) {
                        return !is_null($value) && $value !== '';
                    });
            @endphp

            <!-- Mobile Search Summary -->
            @if(!$isVacation && !$isCheckout)
                <div class="col-12 d-md-none mt-2">
                    <div class="search-summary" role="button" id="headerSearchTrigger">
                        <i class="fas fa-search me-2"></i>
                        @if($activeFilters->isNotEmpty())
                            <span>
                                {{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }} · 
                                @if(request()->has('num_guests') && request()->num_guests > 0)
                                    {{ request()->num_guests }} guests
                                @endif
                                @if(request()->has('target_fish'))
                                    · {{ count((array)request()->target_fish) }} fish
                                @endif
                                @php
                                    // Keys that are already shown above or are not real filters
                                    $ignoredKeys = [
                                        'place', 'placeLat', 'placeLng', 'placelat', 'placelng',
                                        'city', 'country', 'region',
                                        'num_guests', 'target_fish',
                                        'ismobile', 'page',
                                    ];

                                    // Human‑readable labels for known filters
                                    $filterLabels = [
                                        'radius'         => translate('Radius'),
                                        'methods'        => translate('Methods'),
                                        'water'          => translate('Water Types'),
                                        'duration_types' => translate('Duration'),
                                        'num_persons'    => translate('Number of People'),
                                        'price_min'      => translate('Min Price'),
                                        'price_max'      => translate('Max Price'),
                                    ];

                                    // Determine which extra filters are active
                                    $additionalFilterKeys = $activeFilters->except($ignoredKeys)->keys();

                                    $additionalFilterNames = $additionalFilterKeys
                                        ->map(function ($key) use ($filterLabels) {
                                            return $filterLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                                        })
                                        ->unique()
                                        ->values();

                                    $additionalFiltersText = $additionalFilterNames->implode(', ');
                                    // Limit length and add ellipsis if too long
                                    $additionalFiltersText = \Illuminate\Support\Str::limit($additionalFiltersText, 30, '...');
                                @endphp
                                @if($additionalFilterNames->isNotEmpty())
                                    · {{ $additionalFiltersText }}
                                @endif
                            </span>
                        @else
                            <span>@lang('homepage.searchbar-search-placeholder')</span>
                        @endif
                    </div>
                </div>
            @elseif(!($isCheckout))
                <div id="filterContainer" class="col-12 d-md-none pb-3">
                    <form id="global-search1" action="{{ route('vacations.category', ['country' => 'all']) }}" method="get">
                        <div class="vacation-mobile-select-wrap">
                            <i class="fas fa-map-marker-alt vacation-mobile-select-icon"></i>
                            <select class="vacation-mobile-select" name="country" onchange="updateFormAction(this, 'global-search1')">
                                <option value="">{{translate('Select Country')}}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country }}"
                                        {{ ($currentVacationCountry ?? request()->country) == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Categories Row - Desktop -->
        @if(!$isCheckout)
        <div class="row categories-row d-none d-md-block">
            <div class="col-12">
                <div class="d-flex">
                    <a href="{{ route('guidings.index') }}" 
                       class="me-4 text-white text-decoration-none {{ request()->is('guidings*') ? 'active' : '' }}">
                        <i class="fas fa-fish me-2"></i>@lang('homepage.filter-fishing-near-me')
                    </a>
                    <a href="{{ route('vacations.index') }}" 
                       class="me-4 text-white text-decoration-none {{ request()->is('vacations*') ? 'active' : '' }}">
                        <i class="fas fa-map-signs me-2"></i>@lang('homepage.header-vacations')
                    </a>
                    <a href="{{ route($blogPrefix.'.index') }}" 
                       class="me-4 text-white text-decoration-none {{ request()->is('angelmagazin*') ? 'active' : '' }}">
                        <i class="fas fa-book-open me-2"></i>@lang('homepage.filter-magazine')
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Search Row - Floating (Desktop Only) -->
    @if(!($isCheckout))
    <div class="floating-search-container d-none d-md-block">
        <div class="container">
            <form id="global-search" action="{{$isVacation ? route('vacations.category', ['country' => 'all']) : route('guidings.index')}}" method="get" onsubmit="return validateSearch(event, 'searchPlaceShortDesktop')">
                <div class="search-box">
                    <div class="search-row">
                        @if($isVacation)
                            <div class="search-input flex-grow-1">
                                <i class="fa fa-globe input-icon"></i>
                                <select class="form-select" name="country" onchange="updateFormAction(this, 'global-search1')">
                                    <option value="">{{translate('Select Country')}}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ ($currentVacationCountry ?? request()->country) == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="search-input flex-grow-1">
                                <i class="fa fa-search input-icon"></i>
                                <input id="searchPlaceShortDesktop" type="text" 
                                    class="form-control" 
                                    name="place" 
                                    placeholder="@lang('homepage.searchbar-destination')"
                                    value="{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}" 
                                    autocomplete="on">
                                <input type="hidden" id="LocationLatShortDesktop" name="placeLat" value="{{ request()->placeLat }}"/>
                                <input type="hidden" id="LocationLngShortDesktop" name="placeLng" value="{{ request()->placeLng }}"/>
                                <input type="hidden" id="LocationCityShortDesktop" name="city" value="{{ request()->city }}"/>
                                <input type="hidden" id="LocationCountryShortDesktop" name="country" value="{{ request()->country }}"/>
                                <input type="hidden" id="LocationRegionShortDesktop" name="region" value="{{ request()->region }}"/>
                            </div>
                            <div class="search-input" style="width: 200px;">
                                <i class="fa fa-user input-icon"></i>
                                <input type="number" 
                                    class="form-control" 
                                    name="num_guests" 
                                    placeholder="@lang('homepage.searchbar-person')"
                                    value="{{ request()->num_guests }}">
                            </div>
                            <div class="search-input tagify-fish-wrap" style="width: 300px;">
                                <i class="fa fa-fish input-icon tagify-fish-icon"></i>
                                <input class="tagify-fish-input"
                                       id="tagify-fish-short-desktop"
                                       placeholder="@lang('homepage.searchbar-targetfish')...">
                            </div>
                            <div class="my-1 px-0">
                                <button type="submit" class="search-button">@lang('homepage.searchbar-search')</button>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</nav>
{{-- Navbar styles: resources/sass/components/_navbar.scss --}}




<!-- Search Modal for Mobile -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="mobile-search" action="{{$isVacation ? route('vacations.category', ['country' => 'all']) : route('guidings.index')}}" method="get" onsubmit="return validateSearch(event, 'searchPlace')">
                    @if($isVacation)
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Country') }}</label>
                            <div class="position-relative">
                                <i class="fa fa-globe position-absolute top-50 translate-middle-y"></i>
                                <select class="form-select" name="country" onchange="updateFormAction(this, 'global-search1')">
                                    <option value="">{{translate('Select Country')}}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ ($currentVacationCountry ?? request()->country) == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Location') }}</label>
                            <div class="position-relative">
                                <i class="fas fa-search position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                                <input type="text" 
                                    class="form-control ps-5" 
                                    id="searchPlace"
                                    name="place" 
                                    placeholder="@lang('homepage.searchbar-destination')"
                                    value="{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}" 
                                    autocomplete="on">
                                <input type="hidden" id="LocationLat" name="placeLat" value="{{ request()->placeLat }}"/>
                                <input type="hidden" id="LocationLng" name="placeLng" value="{{ request()->placeLng }}"/>
                                <input type="hidden" id="LocationCity" name="city" value="{{ request()->city }}"/>
                                <input type="hidden" id="LocationCountry" name="country" value="{{ request()->country }}"/>
                                <input type="hidden" id="LocationRegion" name="region" value="{{ request()->region }}"/>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ translate('Number of Persons') }}</label>
                            <div class="position-relative">
                                <i class="fas fa-user position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                                <input type="number" 
                                    class="form-control ps-5" 
                                    name="num_guests" 
                                    placeholder="@lang('homepage.searchbar-person')"
                                    value="{{ request()->num_guests }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">@lang('homepage.searchbar-targetfish')</label>
                            <div class="position-relative target-fish-modal-wrap tagify-fish-wrap">
                                <i class="fas fa-fish tagify-fish-icon"></i>
                                <input class="tagify-fish-input"
                                       id="tagify-fish-short-modal"
                                       placeholder="@lang('homepage.searchbar-targetfish')...">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update the Mobile Menu Modal header -->
<div class="modal fade" id="mobileMenuModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <!-- Updated header section -->
            <div class="modal-header mobile-menu-header">
                <div class="d-flex align-items-center">
                    @auth
                        <img src="{{ asset('images/'. Auth::user()->profil_image) ?? asset('images/placeholder_guide.jpg') }}" 
                             class="rounded-circle mobile-menu-profile-image" 
                             alt="Profile">
                        <span class="mobile-menu-username">{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</span>
                    @else
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" 
                             alt="Logo" 
                             class="mobile-menu-logo">
                    @endauth
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Rest of the modal content remains the same -->
            <div class="modal-body p-0">
                <div class="mobile-menu-items">
                    {{-- <a href="{{ route('destination') }}" class="menu-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>@lang('homepage.searchbar-destination')</span>
                    </a> --}}
                    <a href="{{ route('guidings.index') }}" class="menu-item {{ request()->is('guidings*') ? 'active' : '' }}">
                        <i class="fas fa-fish"></i>
                        <span>@lang('homepage.filter-fishing-near-me')</span>
                    </a>
                    <a href="{{ route('vacations.index') }}" class="menu-item {{ request()->is('vacations*') ? 'active' : '' }}">
                        <i class="fas fa-map-signs"></i>
                        <span>@lang('homepage.header-vacations')</span>
                    </a>
                    <a href="{{ route($blogPrefix.'.index') }}" class="menu-item {{ request()->is('angelmagazin*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i>
                        <span>@lang('homepage.filter-magazine')</span>
                    </a>
                    
                    <div class="menu-divider"></div>
                    
                    @auth
                        <a href="{{ route('profile.index') }}" class="menu-item">
                            <i class="fas fa-user"></i>
                            <span>@lang('homepage.header-profile')</span>
                        </a>
                        <a href="{{ route('profile.bookings') }}" class="menu-item">
                            <i class="fas fa-calendar"></i>
                            <span>@lang('profile.bookings')</span>
                        </a>
                        
                        <div class="menu-divider"></div>
                    @endauth
                    
                    <div class="menu-item">
                        <i class="fas fa-envelope"></i>
                        <span>info.catchaguide@gmail.com</span>
                    </div>
                    <div class="menu-item">
                        <i class="fas fa-phone"></i>
                        <span>+49 (0) 15155495574</span>
                    </div>
                    
                    <div class="social-icons">
                        <a href="https://www.facebook.com/CatchAGuide" class="fab fa-facebook-square"></a>
                        <a href="https://wa.me/+49{{env('CONTACT_NUM')}}" class="fab fa-whatsapp"></a>
                        <a href="https://www.instagram.com/catchaguide_official/" class="fab fa-instagram"></a>
                    </div>
                    
                    <div class="menu-divider"></div>
                    
                    <a href="#" class="menu-item" data-bs-toggle="modal" data-bs-target="#languageModal">
                        <i class="fas fa-map-signs"></i>
                        <span>Language <span class="fi fi-{{ array_search(app()->getLocale(), config('app.locales')) }}"></span></span>
                    </a>
                    
                    @auth
                        <div class="menu-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="menu-item text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>@lang('homepage.header-logout')</span>
                            </button>
                        </form>
                    @else
                        <div class="menu-divider"></div>
                        <a href="#" id="signup-header" class="menu-item" data-bs-toggle="modal" data-bs-target="#registerModal" onclick="closeMobileMenu()">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>@lang('homepage.header-signup')</span>
                        </a>
                        <a href="#" id="login-header" class="menu-item" data-bs-toggle="modal" data-bs-target="#loginModal" onclick="closeMobileMenu()">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>@lang('homepage.header-login')</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this new modal for mobile language selection -->
<div class="modal fade" id="languageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('language.switch') }}" method="POST" id="mobile-language-form">
                    @csrf
                    <div class="list-group">
                        @foreach (config('app.locales') as $key => $locale)
                            <button type="button" 
                                    onclick="handleMobileLanguageSwitch('{{ $locale }}')"
                                    class="list-group-item list-group-item-action d-flex align-items-center {{ app()->getLocale() == $locale ? 'active' : '' }}">
                                <span class="fi fi-{{$key}} me-2"></span>
                                {{ strtoupper($locale) }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="language" id="selected-language" value="">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    
function updateFormAction(selectElement, formId) {
    const form = document.getElementById(formId);
    const selectedCountry = selectElement.value;

    if(selectedCountry){
        form.action = "{{ route('vacations.category', ['country' => 'all']) }}".replace('all', selectedCountry);
    }else{
        form.action = "{{ route('guidings.index') }}";
    }
    
    form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchTrigger = document.getElementById('headerSearchTrigger');
    const searchModal = document.getElementById('searchModal');
    
    if (searchTrigger && searchModal) {
        const headerSearchModal = new bootstrap.Modal(searchModal);
        
        searchTrigger.addEventListener('click', function() {
            headerSearchModal.show();
        });
    }

    // Initialize bootstrap-select (language dropdowns etc. – fish targets use Tagify)
    $('.selectpicker').selectpicker({
        style: 'btn-link',
        size: 4
    });
    
    // Add onchange handlers to all country selects
    const countrySelects = {
        'global-search1': document.querySelector('#global-search1 select[name="country"]'),
        'global-search': document.querySelector('#global-search select[name="country"]'),
        'mobile-search': document.querySelector('#mobile-search select[name="country"]')
    };

    for (const [formId, select] of Object.entries(countrySelects)) {
        if (select) {
            select.addEventListener('change', function() {
                updateFormAction(this, formId);
            });
        }
    }
    
    document.querySelectorAll('.logout-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'  // This explicitly marks it as an AJAX request
                },
                body: JSON.stringify({
                    _token: document.querySelector('meta[name="csrf-token"]').content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Add input event listeners to place search inputs
    ['searchPlace', 'searchPlaceShortDesktop'].forEach(inputId => {
        const searchInput = document.getElementById(inputId);
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const form = this.closest('form');
                const hiddenInputs = [
                    form.querySelector('input[name="placeLat"]'),
                    form.querySelector('input[name="placeLng"]'),
                    form.querySelector('input[name="city"]'),
                    form.querySelector('input[name="country"]'),
                    form.querySelector('input[name="region"]')
                ];

                // Clear all hidden inputs if they have values
                if (hiddenInputs.some(input => input && input.value)) {
                    hiddenInputs.forEach(input => {
                        if (input) input.value = '';
                    });
                }
            });
        }
    });
});

function closeMobileMenu() {
    // Close the mobile menu modal before opening login modal
    const mobileMenuModal = bootstrap.Modal.getInstance(document.getElementById('mobileMenuModal'));
    if (mobileMenuModal) {
        mobileMenuModal.hide();
    }
}

function validateSearch(event, inputId) {
    const searchInput = document.getElementById(inputId);

    const form = searchInput.closest('form');
    const lat = form.querySelector('input[name="placeLat"]').value;
    const lng = form.querySelector('input[name="placeLng"]').value;

    if ( searchInput.value != "" && (!lat || !lng)) {
        // Create and show tooltip only when validation fails
        const tooltip = new bootstrap.Tooltip(searchInput, {
            title: "{{translate('Please select a location from the suggestions')}}",
            placement: "bottom",
            trigger: "manual"
        });
        
        tooltip.show();
        
        // Hide tooltip after 3 seconds
        setTimeout(() => tooltip.dispose(), 3000);

        // Add shake animation to input
        searchInput.classList.add('shake');
        setTimeout(() => searchInput.classList.remove('shake'), 500);

        event.preventDefault();
        return false;
    }

    return true;
}

function handleLanguageSwitch(selectElement, formId) {
    const form = document.getElementById(formId);
    const selectedLanguage = selectElement.value;
    
    // Add the current clean URL as a hidden input
    const currentUrl = window.location.pathname;
    
    // Remove existing redirect_url input if any
    const existingInput = form.querySelector('input[name="redirect_url"]');
    if (existingInput) {
        existingInput.remove();
    }
    
    // Add clean URL as redirect target
    const redirectInput = document.createElement('input');
    redirectInput.type = 'hidden';
    redirectInput.name = 'redirect_url';
    redirectInput.value = currentUrl;
    form.appendChild(redirectInput);
    
    form.submit();
}

function handleMobileLanguageSwitch(language) {
    const form = document.getElementById('mobile-language-form');
    const languageInput = document.getElementById('selected-language');
    const currentUrl = window.location.pathname;
    
    // Set the selected language
    languageInput.value = language;
    
    // Remove existing redirect_url input if any
    const existingInput = form.querySelector('input[name="redirect_url"]');
    if (existingInput) {
        existingInput.remove();
    }
    
    // Add clean URL as redirect target
    const redirectInput = document.createElement('input');
    redirectInput.type = 'hidden';
    redirectInput.name = 'redirect_url';
    redirectInput.value = currentUrl;
    form.appendChild(redirectInput);
    
    // Close the modal before submitting
    const languageModal = bootstrap.Modal.getInstance(document.getElementById('languageModal'));
    if (languageModal) {
        languageModal.hide();
    }
    
    form.submit();
}

// ── Tagify Fish Target Search ──────────────────────────────────────────────
(function () {
    @php
        $targetsForJs = collect(targets()::getAllTargets())->sortBy('name')->values()
            ->map(fn($t) => ['value' => (string)$t['id'], 'label' => $t['name']]);
    @endphp

    var fishWhitelist = @json($targetsForJs);
    var selectedFish  = @json(array_map('strval', (array)(request()->target_fish ?? [])));

    function syncHiddenInputs(tagify, form) {
        form.querySelectorAll('.tagify-fish-hidden').forEach(function (el) { el.remove(); });
        tagify.value.forEach(function (tag) {
            var inp = document.createElement('input');
            inp.type      = 'hidden';
            inp.name      = 'target_fish[]';
            inp.value     = tag.value;
            inp.className = 'tagify-fish-hidden';
            form.appendChild(inp);
        });
    }

    function initFishTagify(inputEl) {
        if (!inputEl || inputEl._tagifyInited) return;
        inputEl._tagifyInited = true;

        var tagify = new Tagify(inputEl, {
            enforceWhitelist : true,
            whitelist        : fishWhitelist,
            maxTags          : 15,
            tagTextProp      : 'label',
            dropdown         : {
                maxItems      : 50,
                enabled       : 0,
                closeOnSelect : false,
                searchKeys    : ['label'],
                classname     : 'tagify__dropdown--fish',
                position      : 'all'
            },
            templates        : {
                dropdownItem : function (item) {
                    var cls  = this.settings.classNames.dropdownItem;
                    var text = item.label || item.value;
                    // getAttributes() writes the data-* attrs Tagify needs to identify the clicked item
                    return '<div ' + this.getAttributes(item) + ' class="' + cls + '" tabindex="0" role="option" aria-selected="false">' + text + '</div>';
                }
            }
        });

        // Pre-select values carried in the URL query string
        if (selectedFish && selectedFish.length > 0) {
            var preSelected = fishWhitelist.filter(function (item) {
                return selectedFish.indexOf(item.value) !== -1;
            });
            if (preSelected.length > 0) {
                tagify.addTags(preSelected);
            }
        }

        // Keep hidden inputs in sync so form submission works correctly
        var form = inputEl.closest('form');
        if (form) {
            tagify.on('add',    function () { syncHiddenInputs(tagify, form); });
            tagify.on('remove', function () { syncHiddenInputs(tagify, form); });
            syncHiddenInputs(tagify, form);
        }

        return tagify;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tagify-fish-input').forEach(initFishTagify);
    });
})();
</script>