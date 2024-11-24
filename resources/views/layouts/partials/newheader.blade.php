<nav class="navbar-custom short-header {{ request()->is('/') ? 'with-bg' : '' }} {{ request()->is('guidings*') ? 'no-search' : '' }}">
    <div class="container">
        <!-- Top Row -->
        <div class="row align-items-center">
            <!-- Logo and Navigation -->
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('welcome') }}">
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Logo" style="height: 45px;">
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="d-none d-md-flex align-items-center top-nav-items">
                    <a href="{{ route('additional.contact') }}" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                    </a>
                    <div class="nav-link language-selector">
                        <i class="fas fa-globe"></i>
                        <span>EN</span>
                    </div>
                    
                    <a href="#" class="nav-link become-guide-link">
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
                        <a href="{{ route('register') }}" class="btn btn-outline-light signup-btn">
                            @lang('homepage.header-signup')
                        </a>
                    @endauth
                </div>

                <!-- Mobile Icons -->
                <div class="d-flex d-md-none">
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
                    <a href="#" class="mobile-nav__toggler text-white">
                        <i class="fas fa-bars" style="font-size: 20px;"></i>
                    </a>
                </div>
            </div>
            
            <div class="header-contents container">
                <h1 class="h2 mt-5">@yield('header_title')</h1>
                <p>@yield('header_sub_title')</p>
            </div>
            
            <!-- Categories Row - Mobile -->
            <div class="col-12 d-md-none mt-3">
                <div class="d-flex categories-mobile">
                    <a href="{{ route('destination') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-marker-alt me-2"></i>@lang('homepage.searchbar-destination')
                    </a>
                    <a href="{{ route('guidings.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-fish me-2"></i>@lang('homepage.filter-fishing-near-me')
                    </a>
                    <a href="{{ route('blog.index') }}" class="text-white text-decoration-none">
                        <i class="fas fa-book-open me-2"></i>@lang('homepage.filter-magazine')
                    </a>
                </div>
            </div>

            <!-- Mobile Search Summary -->
            <div class="col-12 d-md-none mt-3">
                <div class="search-summary" role="button" id="headerSearchTrigger">
                    <i class="fas fa-search me-2"></i>
                    @if(request()->has('place'))
                        <span>{{ request()->place }} · 
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
                    <a href="{{ route('destination') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-marker-alt me-2"></i>@lang('homepage.searchbar-destination')
                    </a>
                    <a href="{{ route('guidings.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-fish me-2"></i>@lang('homepage.filter-fishing-near-me')
                    </a>
                    <a href="{{ route('blog.index') }}" class="me-4 text-white text-decoration-none">
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
            <form id="global-search" action="{{route('guidings.index')}}" method="get">
                <div class="search-box">
                    <div class="d-flex">
                        <div class="search-input flex-grow-1">
                            <i class="fa fa-search input-icon"></i>
                            <input type="text" 
                                   class="form-control" 
                                   name="place" 
                                   placeholder="@lang('homepage.searchbar-destination')"
                                   value="{{ request()->place }}">
                            <input type="hidden" id="placeLat" name="placeLat" value="{{ request()->placeLat }}"/>
                            <input type="hidden" id="placeLng" name="placeLng" value="{{ request()->placeLng }}"/>
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
                                @foreach(targets()::getAllTargets() as $target)
                                    <option value="{{$target['id']}}" 
                                        {{ in_array($target['id'], (array)request()->target_fish) ? 'selected' : '' }}>
                                        {{$target['name']}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 my-1 px-0">
                            <button type="submit" class="search-button">@lang('homepage.searchbar-search')</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
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
    z-index: 1000;
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
        padding: 12px 20px !important;
        margin: 0 !important;
    }
    
    .short-header.navbar-custom .header-contents h1 {
        font-size: 24px !important;
        margin-top: 8px !important;
        margin-bottom: 4px !important;
    }

    .short-header.navbar-custom .header-contents p {
        margin-bottom: 8px !important;
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
        margin: 0 20px !important;
        padding: 12px !important;
        background: rgba(255, 255, 255, 0.1) !important;
        border-radius: 8px !important;
        color: white !important;
        cursor: pointer !important;
    }

    .short-header.navbar-custom .search-summary i {
        margin-right: 8px !important;
    }
}

/* Desktop Header Styles */
@media (min-width: 768px) {
    .short-header .container {
        max-width: 1200px;
        padding: 0 15px;
    }

    .short-header .top-nav-items {
        gap: 24px;
        height: 45px;
    }

    .short-header .logo img {
        height: 40px;
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
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
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
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .short-header .signup-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: white;
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
                <form id="mobile-search" action="{{route('guidings.index')}}" method="get">
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <div class="position-relative">
                            <i class="fas fa-search position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                            <input type="text" 
                                   class="form-control ps-5" 
                                   name="place" 
                                   placeholder="@lang('homepage.searchbar-destination')"
                                   value="{{ request()->place }}">
                            <input type="hidden" name="placeLat" value="{{ request()->placeLat }}"/>
                            <input type="hidden" name="placeLng" value="{{ request()->placeLng }}"/>
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
                                @foreach(targets()::getAllTargets() as $target)
                                    <option value="{{$target['id']}}"
                                        {{ in_array($target['id'], (array)request()->target_fish) ? 'selected' : '' }}>
                                        {{$target['name']}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchTrigger = document.getElementById('headerSearchTrigger');
    const searchModal = document.getElementById('searchModal');
    
    if (searchTrigger && searchModal) {
        const headerSearchModal = new bootstrap.Modal(searchModal);
        
        searchTrigger.addEventListener('click', function() {
            headerSearchModal.show();
        });
    }
});
</script>