<nav class="navbar-custom short-header {{ request()->is('/') ? 'with-bg' : '' }} {{ request()->is('guidings*') ? 'no-search' : '' }}">
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
                    <a href="{{ route('additional.contact') }}" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                    </a>
                    <div class="nav-link language-selector">
                        <form action="{{ route('language.switch') }}" method="POST" class="d-flex align-items-center">
                            @csrf
                            <i class="fas fa-map-signs me-2"></i>
                            <select name="language" class="selectpicker header-language-select" data-width="fit" onchange="this.form.submit()">
                                @foreach (config('app.locales') as $key => $locale)
                                    <option value="{{ $locale }}" 
                                            data-content='<span class="fi fi-{{$key}}"></span>' 
                                            {{ app()->getLocale() == $locale ? 'selected' : '' }}>
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    
                    <a href="{{ route('login') }}" class="nav-link become-guide-link">
                        @lang('homepage.header-become-guide')
                    </a>
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
                                <form method="POST" action="{{ route('admin.auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> @lang('homepage.header-logout')
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="nav-link login-link">
                            @lang('homepage.header-login')
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light signup-btn">
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
                        <a href="{{ route('login') }}" class="text-white me-3">
                            <i class="far fa-user-circle" style="font-size: 24px;"></i>
                        </a>
                    @endauth
                    <a href="#" class="text-white" data-bs-toggle="modal" data-bs-target="#mobileMenuModal">
                        <i class="fas fa-bars" style="font-size: 24px;"></i>
                    </a>
                </div>
            </div>
            
            <!-- Categories Row - Mobile -->
            <div class="col-12 d-md-none mt-2">
                <div class="d-flex categories-mobile">
                    {{-- <a href="{{ route('destination') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-marker-alt me-2"></i>@lang('homepage.searchbar-destination')
                    </a> --}}
                    <a href="{{ route('guidings.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-fish me-2"></i>@lang('homepage.filter-fishing-near-me')
                    </a>
                    <a href="{{ route('vacations.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-signs me-2"></i>@lang('homepage.header-vacations')
                    </a>
                    <a href="{{ route($blogPrefix.'.index') }}" class="text-white text-decoration-none">
                        <i class="fas fa-book-open me-2"></i>@lang('homepage.filter-magazine')
                    </a>
                </div>
            </div>

            <!-- Mobile Search Summary -->
            <div class="col-12 d-md-none mt-2">
                <div class="search-summary" role="button" id="headerSearchTrigger">
                    <i class="fas fa-search me-2"></i>
                    @if(request()->has('place'))
                        <span>{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }} · 
                            {{ request()->num_guests ?? '0' }} guests
                            @if(request()->has('target_fish'))
                                · {{ count((array)request()->target_fish) }} fish
                            @endif
                        </span>
                    @else
                        <span>@lang('homepage.searchbar-search-placeholder')</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Categories Row - Desktop -->
        <div class="row categories-row d-none d-md-block">
            <div class="col-12">
                <div class="d-flex">
                    {{-- <a href="{{ route('destination') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-marker-alt me-2"></i>@lang('homepage.searchbar-destination')
                    </a> --}}
                    <a href="{{ route('guidings.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-fish me-2"></i>@lang('homepage.filter-fishing-near-me')
                    </a>
                    <a href="{{ route('vacations.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-signs me-2"></i>@lang('homepage.header-vacations')
                    </a>
                    <a href="{{ route($blogPrefix.'.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-book-open me-2"></i>@lang('homepage.filter-magazine')
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Row - Floating (Desktop Only) -->
    <div class="floating-search-container d-none d-md-block">
        <div class="container">
            <form id="global-search" action="{{$isVacation ? route('vacations.category', ['country' => 'all']) : route('guidings.index')}}" method="get">
                <div class="search-box">
                    <div class="search-row">
                        @if($isVacation)
                            <div class="search-input flex-grow-1">
                                <i class="fa fa-globe input-icon"></i>
                                <select class="form-select" name="country" onchange="updateFormAction(this, 'global-search1')">
                                    <option value="">{{translate('Select Country')}}</option>
                                    @php
                                        $countries = \App\Models\Vacation::select('country')
                                            ->where('status', 1)
                                            ->distinct()
                                            ->pluck('country')
                                            ->sort();
                                    @endphp
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ request()->country == $country ? 'selected' : '' }}>
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
                                    value="{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}" autocomplete="on">
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
                            <div class="search-input" style="width: 300px;">
                                <i class="fa fa-fish input-icon"></i>
                                <select class="form-select" name="target_fish[]" id="target_fish_search">
                                    <option value="">@lang('homepage.searchbar-targetfish')...</option>
                                    @php
                                        // Assuming targets()::getAllTargets() returns an array or collection of targets
                                        $targets = collect(targets()::getAllTargets())->sortBy('name');
                                    @endphp
                                    @foreach($targets as $target)
                                        <option value="{{ $target['id'] }}" 
                                            {{ in_array($target['id'], (array) request()->target_fish) ? 'selected' : '' }}>
                                            {{ $target['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="my-1 px-0">
                            <button type="submit" class="search-button">@lang('homepage.searchbar-search')</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</nav>

<style>
.short-header.navbar-custom {
    background-color: #313041;
    padding: 16px 0 35px;
    position: relative;
    margin-bottom: 60px;
}

.short-header .floating-search-container {
    position: absolute;
    left: 0;
    right: 0;
    bottom: -30px;
    z-index: 1;
}

 .search-box .search-row{
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.short-header .search-box {
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.short-header .search-input {
    position: relative;
    margin-right: 12px;
    min-height: 48px;
    display: flex;
    align-items: center;
}

.short-header .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 1;
    pointer-events: none;
}

.short-header .form-control,
.short-header .form-select {
    height: 48px;
    padding-left: 40px;
    border: 1px solid #E85B40;
    border-radius: 4px;
    width: 100%;
}

.short-header .form-control:focus,
.short-header .form-select:focus {
    box-shadow: none;
    border-color: #E85B40;
}

.short-header .search-button {
    background-color: #E85B40;
    color: white;
    border: none;
    height: 48px;
    padding: 0 24px;
    border-radius: 4px;
    min-width: 120px;
    white-space: nowrap;
}

/* Remove number input arrows */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

/* Mobile Styles - with specific selectors */
@media (max-width: 767px) {
    .short-header.navbar-custom {
        padding: 16px 0 ;
    }
    /* Categories container with hidden scrollbar */
    .short-header.navbar-custom .categories-mobile {
        margin: 8px 20px !important;
        padding: 0 !important;
        display: flex !important;
        gap: 8px !important;
        overflow-x: auto !important;
        -ms-overflow-style: none !important;  /* IE and Edge */
        scrollbar-width: none !important;  /* Firefox */
    }
    
    /* Hide scrollbar for Chrome, Safari and Opera */
    .short-header.navbar-custom .categories-mobile::-webkit-scrollbar {
        display: none !important;
    }

    /* Individual category items */
    .short-header.navbar-custom .categories-mobile a {
        padding: 6px 12px !important;
        margin-right: 8px !important;
        white-space: nowrap !important;
        font-size: 14px !important;
        background-color: rgba(255, 255, 255, 0.1) !important;
        border-radius: 50px !important;
    }

    .short-header.navbar-custom .categories-mobile a:last-child {
        margin-right: 0 !important;
    }

    /* Search bar fixes */
    .short-header.navbar-custom .search-summary {
        margin: 0 !important;
        padding: 12px !important;
        background: #fff;
        border-radius: 8px !important;
        color: #313041 !important;
        cursor: pointer !important;
    }

    .short-header.navbar-custom .search-summary i {
        margin-right: 8px !important;
    }
    form#mobile-search .btn-primary{
        background-color: #313041;
        border-color: #E8604C !important;
    }
}

/* Desktop Header Styles */
@media (min-width: 768px) {
    .short-header .container {
        max-width: 1200px;
        padding: 0 15px;
    }

    .short-header .top-nav-items {
        gap: 8px;
        height: 45px;
    }

    .short-header .logo img {
        height: 45px;
        margin-top: 4px;
    }

    .short-header .floating-search-container .container {
        max-width: 1200px;
        padding: 0 15px;
    }

    .short-header .search-box {
        margin: 0;
        width: 100%;
    }

    .categories-row {
        margin-left: 0;
        margin-right: 0;
        padding: 0 15px;
    }

    .categories-row .col-12 {
        padding: 0;
    }

    .short-header .top-nav-items .nav-link {
        color: white;
        text-decoration: none;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 500;
        border-radius: 4px;
        transition: background-color 0.2s;
    }

    .short-header .top-nav-items .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .short-header .signup-btn {
        border: 1.5px solid rgba(255, 255, 255, 0.5);
        padding: 8px 16px;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .short-header .signup-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: white;
        color: white;
    }

    .header-desktop-profile .dropdown-menu {
        margin-top: 8px;
        right: 0;
        left: auto;
        min-width: 200px;
        padding: 8px 0;
        font-size: 15px;
    }

    .header-desktop-profile .dropdown-item {
        padding: 8px 16px;
    }
}

/* Categories styling */
.categories-row a,
.categories-mobile a {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 50px;
    transition: background-color 0.2s;
    margin-right: 16px;
}

.categories-row a:hover,
.categories-mobile a:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.categories-row {
    margin-top: 16px !important;
    margin-bottom: 32px !important;
    position: relative;
    z-index: 1;
}

/* Profile Image Styles - Scoped to navbar-custom header only */
.navbar-custom .header-desktop-profile .profile-image {
    width: 32px;
    height: 32px;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.navbar-custom .header-desktop-profile .nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
}

.navbar-custom .header-desktop-profile .nav-link span {
    color: white;
    font-weight: 500;
}

/* Ensure dropdown toggle arrow is properly aligned */
.navbar-custom .header-desktop-profile .dropdown-toggle::after {
    margin-left: 8px;
    vertical-align: middle;
}

/* Add these styles */
.mobile-menu-items {
    padding: 20px;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 15px;
    color: #333;
    text-decoration: none;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.menu-item i {
    width: 24px;
    margin-right: 15px;
}

.menu-divider {
    height: 1px;
    background-color: #eee;
    margin: 10px 0;
}

.social-icons {
    display: flex;
    gap: 20px;
    padding: 15px;
}

.social-icon {
    color: #333;
    font-size: 20px;
}

/* Ensure the burger menu is vertically centered */
.mobile-nav__toggler {
    display: flex;
    align-items: center;
    height: 32px;
}

/* Add these new styles */
.mobile-menu-header {
    background-color: #313041;
    padding: 1rem;
}

.mobile-menu-profile-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    margin-right: 12px;
}

.mobile-menu-username {
    color: white;
    font-size: 16px;
    font-weight: 500;
}

.mobile-menu-logo {
    height: 40px;
}

/* Style for the close button */
.mobile-menu-header .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* Scoped styles for mobile menu */
#mobileMenuModal .mobile-menu-header {
    background-color: #313041;
    padding: 0.75rem 1rem;
    border-bottom: none;
}

#mobileMenuModal .mobile-menu-profile-image {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 50%;
    margin-right: 12px;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

#mobileMenuModal .mobile-menu-username {
    color: white;
    font-size: 18px;
    font-weight: 500;
}

#mobileMenuModal .mobile-menu-logo {
    height: 45px;
}

#mobileMenuModal .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
    opacity: 0.8;
}

#mobileMenuModal .mobile-menu-items {
    padding: 0.5rem 1rem;
}

#mobileMenuModal .menu-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    color: #333;
    text-decoration: none;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 16px;
}

#mobileMenuModal .menu-item i {
    width: 24px;
    margin-right: 12px;
    font-size: 18px;
}

#mobileMenuModal .menu-divider {
    height: 1px;
    background-color: #eee;
    margin: 8px 0;
}

#mobileMenuModal .social-icons {
    display: flex;
    gap: 20px;
    padding: 12px 0;
}

#mobileMenuModal .social-icon {
    color: #333;
    font-size: 20px;
    text-decoration: none;
}

#mobileMenuModal .social-icon:hover {
    color: #E85B40;
}

/* Ensure the burger menu is vertically centered in header */
.navbar-custom .mobile-nav__toggler {
    display: flex;
    align-items: center;
    height: 32px;
}

/* Remove default modal padding */
#mobileMenuModal .modal-body {
    padding: 0;
}

/* Hover effects */
#mobileMenuModal .menu-item:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

#mobileMenuModal .menu-item.text-danger:hover {
    color: #dc3545 !important;
}

/* Desktop Language Selector */
.navbar-custom .header-language-select {
    background: transparent;
    border: none;
    color: white;
    padding-right: 15px;
}

.navbar-custom .header-language-select option {
    background-color: white;
    color: #333;
}

/* Override bootstrap-select styles for header */
.navbar-custom .selectpicker {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

.navbar-custom .bootstrap-select .dropdown-toggle:focus {
    outline: none !important;
    box-shadow: none !important;
}

.navbar-custom .bootstrap-select > .dropdown-toggle {
    border: none !important;
}

.navbar-custom .selectpicker .filter-option {
    display: flex;
    align-items: center;
}

.navbar-custom .selectpicker .filter-option .fi {
    font-size: 1.2em;
}

/* Language Modal Styles */
#languageModal .modal-content {
    border-radius: 8px;
}

#languageModal .list-group-item {
    border: none;
    padding: 12px 16px;
}

#languageModal .list-group-item.active {
    background-color: #E85B40;
    border-color: #E85B40;
}

#languageModal .fi {
    font-size: 1.2em;
}

/* Hide default select arrow in modern browsers */
.header-language-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Hide default select arrow in IE */
.header-language-select::-ms-expand {
    display: none;
}
</style>

<!-- Search Modal for Mobile -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="mobile-search" action="{{$isVacation ? route('vacations.category', ['country' => 'all']) : route('guidings.index')}}" method="get">
                    @if($isVacation)
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Country') }}</label>
                            <div class="position-relative">
                                <i class="fa fa-globe position-absolute top-50 translate-middle-y"></i>
                                <select class="form-select" name="country" onchange="updateFormAction(this, 'global-search1')">
                                    <option value="">{{translate('Select Country')}}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ request()->country == $country ? 'selected' : '' }}>
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
                                    value="{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}" autocomplete="on">
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
                            <label class="form-label">Target Fish</label>
                            <div class="position-relative">
                                <i class="fas fa-fish position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                                <select class="form-select ps-5" name="target_fish[]">
                                    <option value="">Select fish...</option>
                                    @php
                                        // Assuming targets()::getAllTargets() returns an array or collection of targets
                                        $targets = collect(targets()::getAllTargets())->sortBy('name');
                                    @endphp
                                    @foreach($targets as $target)
                                        <option value="{{ $target['id'] }}" 
                                            {{ in_array($target['id'], (array) request()->target_fish) ? 'selected' : '' }}>
                                            {{ $target['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary w-100">Search</button>
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
                    <a href="{{ route('guidings.index') }}" class="menu-item">
                        <i class="fas fa-fish"></i>
                        <span>@lang('homepage.filter-fishing-near-me')</span>
                    </a>
                    <a href="{{ route('vacations.index') }}" class="menu-item">
                        <i class="fas fa-map-signs"></i>
                        <span>@lang('homepage.header-vacations')</span>
                    </a>
                    <a href="{{ route($blogPrefix.'.index') }}" class="menu-item">
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
                        <form method="POST" action="{{ route('admin.auth.logout') }}">
                            @csrf
                            <button type="submit" class="menu-item text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>@lang('homepage.header-logout')</span>
                            </button>
                        </form>
                        @else

                        <div class="menu-divider"></div>
                        <a href="{{ route('login') }}"  type="submit" class="menu-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>@lang('homepage.header-signup')</span>
                        </a>
                        <div class="menu-divider"></div>
                            <a href="{{ route('login') }}" type="submit" class="menu-item">
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
                <form action="{{ route('language.switch') }}" method="POST">
                    @csrf
                    <div class="list-group">
                        @foreach (config('app.locales') as $key => $locale)
                            <button type="submit" 
                                    name="language" 
                                    value="{{ $locale }}" 
                                    class="list-group-item list-group-item-action d-flex align-items-center {{ app()->getLocale() == $locale ? 'active' : '' }}">
                                <span class="fi fi-{{$key}} me-2"></span>
                                {{ strtoupper($locale) }}
                            </button>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    
function updateFormAction(selectElement, formId) {
    const form = document.getElementById(formId);
    const selectedCountry = selectElement.value;
    
    // Use 'all' as default if no country is selected
    const country = selectedCountry || 'all';
    form.action = "{{ route('vacations.category', ['country' => 'all']) }}".replace('all', country);
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

    // Initialize bootstrap-select
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
});
</script>