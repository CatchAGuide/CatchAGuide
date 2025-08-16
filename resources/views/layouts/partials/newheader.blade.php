@include('layouts.modal.loginModal')
@include('layouts.modal.registerModal')
<nav class="navbar-custom short-header long-header {{ request()->is('/') ? 'with-bg' : '' }} {{ request()->is('guidings*') ? 'no-search' : '' }}">
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
                            <div class="dropdown-menu dropdown-menu-end">
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
            
            <div class="header-contents container">
                <h1 class="h2 mt-5">@yield('header_title')</h1>
                <p>@yield('header_sub_title')</p>
            </div>
            
            <!-- Categories Row - Mobile -->
            <div class="col-12 d-md-none mt-1">
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
                       class="text-white text-decoration-none {{ request()->is('angelmagazin*') ? 'active' : '' }}">
                        <i class="fas fa-book-open me-2"></i>@lang('homepage.filter-magazine')
                    </a>
                </div>
            </div>

            <!-- Mobile Search Summary -->
            <div id="filterContainer" class="col-12 d-md-none mt-3">
            <form class="search-form row gx-2 pe-0" id="global-search1" action="{{ $isVacation ? route('vacations.category', ['country' => 'all']) : route('guidings.index') }}" method="get" onsubmit="return validateSearch(event, 'searchPlaceMobile')">                
                <div id="mobileherofilter" class="shadow-lg bg-white p-2 rounded">
                    <div class="row">
                        @if ($isVacation)
                        {{-- <div class="vacation-header"> --}}
                            {{-- <div class="col-md-4 column-input my-2"> --}}
                                <div class="d-flex align-items-center small myselect2">
                                    <i class="fa fa-map-marker-alt position-absolute ps-1"></i>
                                    <select class="form-control form-select border-0" name="country" onchange="updateFormAction(this, 'global-search1')">
                                        <option value="">{{translate('Select Country')}}</option>
                                        @php
                                            $countries = \App\Models\Destination::where('type', 'vacations')->where('language',app()->getLocale())->pluck('name');
                                        @endphp
                                        @foreach($countries as $country)
                                            <option value="{{ $country }}" 
                                                {{ request()->country == $country ? 'selected' : '' }}>
                                                {{ $country }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            {{-- </div>
                            <div class="">
                            <button type="submit" class="form-control new-filter-btn mobile"><i class="icon-magnifying-glass"></i></button>
                            </div> --}}
                        {{-- </div> --}}
                        @else
                            <div class="col-md-4 column-input my-2">
                                <div class="form-group">
                                    <div class="d-flex align-items-center small">
                                        <i class="fa fa-search fa-fw text-muted position-absolute ps-2"></i>
                                        <input  id="searchPlaceMobile" name="place" type="text" class="form-control rounded-0" placeholder="@lang('homepage.searchbar-destination')" autocomplete="on">
                                        <input type="hidden" id="LocationCityMobile" name="city" value="{{ request()->city }}"/>
                                        <input type="hidden" id="LocationCountryMobile" name="country" value="{{ request()->country }}"/>
                                        <input type="hidden" id="LocationRegionMobile" name="region" value="{{ request()->region }}"/>
                                        <input type="hidden" id="LocationLatMobile" name="placeLat" value="{{ request()->placeLat }}"/>
                                        <input type="hidden" id="LocationLngMobile" name="placeLng" value="{{ request()->placeLng }}"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 column-input my-2">
                                <div class="form-group">
                                    <div class="d-flex align-items-center small">
                                        <i class="fa fa-user fa-fw text-muted position-absolute ps-2"></i>
                                        <input type="number" min="1" max="5" class="form-control rounded-0" name="num_guests" placeholder="@lang('homepage.searchbar-person')" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 column-input my-2">
                                <div class="d-flex align-items-center small myselect2">
                                    <i class="fa fa-fish fa-fw text-muted position-absolute ps-1"></i>
                                    <select class="form-control form-select border-0" name="target_fish[]" style="width:100%">
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
                            <div class="col-md-2 col-12 column-button my-2">
                                        <button type="submit" class="form-control new-filter-btn">@lang('homepage.searchbar-search')</button>
                            </div>
                        @endif
                    </div>
                </div> 
            </form>
            </div>
        </div>

        <!-- Categories Row - Desktop -->
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
    </div>

    <!-- Search Row - Floating (Desktop Only) -->
    @if(request()->segment(1) != 'guidings')
    <div class="floating-search-container d-none d-md-block">
        <div class="container">
            <form id="global-search" action="{{ $isVacation ? route('vacations.category', ['country' => 'all']) : route('guidings.index') }}" method="get" onsubmit="return validateSearch(event, 'searchPlaceDesktop')">
                <div class="search-box">
                    <div class="search-row">
                        @if ($isVacation)
                            <div class="search-input flex-grow-1">
                                <i class="fa fa-globe input-icon"></i>
                                <select class="form-select" name="country" onchange="updateFormAction(this, 'global-search1')">
                                    <option value="">{{translate('Select Country')}}</option>
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
                                <input id="searchPlaceDesktop" type="text" 
                                    class="form-control" 
                                    name="place" 
                                    placeholder="@lang('homepage.searchbar-destination')"
                                    value="{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}"
                                    autocomplete="on"/>
                                <input type="hidden" id="LocationLatDesktop" name="placeLat" value="{{ request()->placeLat }}"/>
                                <input type="hidden" id="LocationLngDesktop" name="placeLng" value="{{ request()->placeLng }}"/>
                                <input type="hidden" id="LocationCityDesktop" name="city" value="{{ request()->city }}"/>
                                <input type="hidden" id="LocationCountryDesktop" name="country" value="{{ request()->country }}"/>
                                <input type="hidden" id="LocationRegionDesktop" name="region" value="{{ request()->region }}"/>
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

<style>
    #filterContainer{
        height: {{ $isVacation ? '70px' : '200px' }};
    }
    #mobileherofilter {
    position: absolute;
    left: 0;
    right: 0;
    width: 95%;
    margin: auto;
}
    #mobileherofilter:has(.vacation-header) .new-filter-btn{
        display: none;
    }
     #mobileherofilter .column-input input{
        border-top:none !important;
        border-left:none !important;
        border-right:none !important;
        border-bottom:1px solid #a7a7a7 !important;
        outline:none !important;
    }
    #mobileherofilter .column-input i{
        color:#E8604C !important;
    }
    #mobileherofilter .column-input input,select{
       padding-left:30px !important;
    }
    #mobileherofilter .form-control:focus {
        border-color: inherit;
        -webkit-box-shadow: none;
        box-shadow: none;
        outline:none !important;
    }
    #mobileherofilter .myselect2{
        border-top:none !important;
        border-left:none !important;
        border-right:none !important;
        border-bottom:1px solid #a7a7a7 !important;
        /* border-bottom:2px solid #E8604C !important; */
        padding:2px 0px;
        border-width: 1px !important;
        background-color: white;
    }
    #mobileherofilter .myselect2 li.select2-selection__choice{
            background-color: #313041 !important;
            color: #fff !important;
            border: 0 !important;
            font-size:14px;
            vertical-align: middle !important;
            margin-top:0 !important;
         
    }
    #mobileherofilter .myselect2 button.select2-selection__choice__remove{
        border: 0 !important;
        color: #fff !important;
    }
    #mobileherofilter .new-filter-btn{
        background-color:#E8604C;
        color:#fff;
        padding: 6px 12px;
    }
    #mobileherofilter .new-filter-btn:hover{
        background-color:#313041;
    }
.short-header.long-header.navbar-custom {
    background-color: #313041;
    padding: 16px 0 35px;
    position: relative;
    margin-bottom: 30px;
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
    .short-header.navbar-custom .header-contents {
        padding: 12px  !important;
        margin: 0 !important;
    }
    
    .short-header.navbar-custom .header-contents h1 {
        font-size: 24px !important;
        margin-top: 8px !important;
        margin-bottom: 4px !important;
    }

    .short-header.navbar-custom .header-contents p {
        margin-bottom: 0px !important;
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
        background-color: rgba(255, 255, 255, 0.1);
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
    }
}
@media (max-width: 768px) {
    #mobileherofilter:has(.vacation-header) .new-filter-btn.mobile{
        display: block;
    }
    .vacation-header{
        display: flex;
        align-items: center;
        gap: 4px;
    }
    #filterContainer:has(.vacation-header) .column-input{

    }
    #mobileherofilter:has(.vacation-header) .column-input{
        width: calc(100% - 80px);
        padding-left: 0;
    }
    #filterContainer:has(.vacation-header) #mobileherofilter .column-button{
        display: none;
    }

    #mobileherofilter .row .col-md-4{
        margin: 0!important;
    }
    #mobileherofilter .column-button{
        width: 100%;
        padding: 0 12px;
        margin-top: 10px;
    }
    .new-filter-btn.mobile{
        display: block;
        width: 80px;
        height: 100%;
    }
    #mobileherofilter .new-filter-btn.mobile i{
        color: #fff !important;
    }
}
/* Desktop Header Styles */
@media (min-width: 768px) {
    .new-filter-btn.mobile{
        display: none;
    }
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
    /* margin-top: 16px !important; */
    margin-bottom: 38px !important;
    position: relative;
    z-index: 1;
}

.header-contents {
    padding: 16px 0;
}

.header-contents h1 {
    margin-top: 16px !important;
    margin-bottom: 8px;
    font-size: 28px;
    color: white;
}

.header-contents p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 16px;
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


/* Add these new styles for desktop and mobile active states */
.categories-row a.active,
.categories-mobile a.active,
.mobile-menu-items .menu-item.active {
    background-color: #fff !important;
    color: #313041 !important;
    font-weight: 500;
}
.categories-row a.active i,
.categories-mobile a.active i,
.mobile-menu-items .menu-item.active i {
    color: #E85B40 !important;
}
/* Specific styles for mobile menu active items */

/* Hover effects for non-active items */
.categories-row a:not(.active):hover,
.categories-mobile a:not(.active):hover,
.mobile-menu-items .menu-item:not(.active):hover {
    background-color: #fff;
    color: #313041 !important;
}
.categories-row a:not(.active):hover i,
.categories-mobile a:not(.active):hover i,
.mobile-menu-items .menu-item:not(.active):hover i {
    background-color: #fff;
    color: #E85B40 !important;
}

/* Base styles for mobile menu items */
.mobile-menu-items .menu-item {
    border-radius: 8px;
    margin-bottom: 4px;
    transition: all 0.2s ease;
}

.mobile-menu-items .menu-item i {
    transition: color 0.2s ease;
}

/* Add shake animation */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.shake {
    animation: shake 0.5s ease-in-out;
}

/* Style the tooltip */
.tooltip {
    position: absolute;
    z-index: 1070;
}

.tooltip .tooltip-inner {
    background-color: #dc3545;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
}

.tooltip .tooltip-arrow::before {
    border-bottom-color: #dc3545;
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
                <form id="mobile-search" action="{{ $isVacation ? route('vacations.category', ['country' => 'all']) : route('guidings.index') }}" method="get" onsubmit="return validateSearch(event, 'searchPlaceHeaderDesktop')">
                    @if ($isVacation)
                        <div class="mb-3">
                            <label class="form-label">{{translate('Country')}}</label>
                            <div class="position-relative">
                                <i class="fas fa-globe position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                                <select class="form-select ps-5" name="country" onchange="updateFormAction(this, 'mobile-search')">
                                    <option value="">{{translate('Select Country')}}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" 
                                            {{ request()->country == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <div class="position-relative">
                                <i class="fas fa-search position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                                <input type="text" 
                                    id="searchPlaceHeaderDesktop"
                                    class="form-control ps-5" 
                                    name="place" 
                                    placeholder="@lang('homepage.searchbar-destination')"
                                    autocomplete="on"
                                    value="{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}">
                                <input type="hidden" name="placeLat" id="LocationLatHeaderDesktop" value="{{ request()->placeLat }}"/>
                                <input type="hidden" name="placeLng" id="LocationLngHeaderDesktop" value="{{ request()->placeLng }}"/>
                                <input type="hidden" name="city" id="LocationCityHeaderDesktop" value="{{ request()->city }}"/>
                                <input type="hidden" name="country" id="LocationCountryHeaderDesktop" value="{{ request()->country }}"/>
                                <input type="hidden" name="region" id="LocationRegionHeaderDesktop" value="{{ request()->region }}"/>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Number of Persons</label>
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
                        <a href="#" id="singup-header" class="menu-item" data-bs-toggle="modal" data-bs-target="#registerModal" onclick="closeMobileMenu()">
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
    
    // Use the selected country for the form action if one is selected
    if (selectedCountry) {
        form.action = "{{ route('vacations.category', ['country' => 'all']) }}".replace('all', selectedCountry);
    } else {
        form.action = "{{ route('vacations.index') }}";
    }
    
    // Submit the form immediately after changing the action
    form.submit();
}

function closeMobileMenu() {
    // Close the mobile menu modal before opening login modal
    const mobileMenuModal = bootstrap.Modal.getInstance(document.getElementById('mobileMenuModal'));
    if (mobileMenuModal) {
        mobileMenuModal.hide();
    }
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

    // Update logout form handling
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

    // Add input event listeners to place search inputs
    ['searchPlaceDesktop', 'searchPlaceMobile'].forEach(inputId => {
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
</script>