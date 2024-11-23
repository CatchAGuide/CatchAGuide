<nav class="cag-header-navbar {{ request()->is('/') ? 'cag-header-with-bg' : '' }} {{ request()->is('guidings*') ? 'cag-header-no-search' : '' }}">
    <div class="container">
        <!-- Top Row -->
        <div class="row align-items-center">
            <!-- Logo and Navigation -->
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="cag-header-logo">
                    <a href="{{ route('welcome') }}">
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Logo" style="height: 45px;">
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="d-none d-md-flex align-items-center cag-header-nav-items">
                    <a href="{{ route('additional.contact') }}" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                    </a>
                    <div class="nav-link language-selector">
                        <i class="fas fa-globe"></i>
                        <span>EN</span>
                    </div>
                    <a href="#" class="nav-link become-guide-link">
                        Become a guide
                    </a>
                    @auth
                        <div class="header-desktop-profile dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <img src="{{ asset('images/'. Auth::user()->profil_image) ?? asset('images/placeholder_guide.jpg') }}" 
                                     class="rounded-circle me-2" 
                                     alt="Profile">
                                <span>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="fas fa-user me-2"></i> Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('admin.auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="nav-link login-link">Log in</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-light signup-btn">Sign up</a>
                    @endauth
                </div>

                <!-- Mobile Icons -->
                <div class="d-flex d-md-none">
                    @auth
                        {{-- <a href="#" class="text-white me-3"><i class="fas fa-bell"></i></a> --}} 
                        <div class="dropdown mobile-profile-dropdown me-3">
                            <img src="{{ asset('images/'. Auth::user()->profil_image) ?? asset('images/placeholder_guide.jpg') }}" 
                                 class="rounded-circle" 
                                 style="width: 32px; height: 32px;" 
                                 data-bs-toggle="dropdown"
                                 alt="Profile">
                            <div class="dropdown-menu dropdown-menu-end mobile-profile-menu">
                                <div class="px-3 py-2">
                                    <img src="{{ asset('images/'. Auth::user()->profil_image) ?? asset('images/placeholder_guide.jpg') }}" 
                                         class="rounded-circle me-2" 
                                         style="width: 40px; height: 40px;">
                                    <span>{{ Auth::user()->firstname . ' ' . Auth::user()->lastname }}</span>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="fas fa-user me-2"></i> Manage account
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('admin.auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
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

            <!-- Categories Row - Mobile -->
            <div class="col-12 d-md-none mt-3">
                <div class="d-flex categories-mobile">
                    <a href="#" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-marker-alt me-2"></i>Destination
                    </a>
                    <a href="{{ route('guidings.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-fish me-2"></i>Fishing Near Me
                    </a>
                    <a href="{{ route('blog.index') }}" class="text-white text-decoration-none">
                        <i class="fas fa-book-open me-2"></i>Magazine
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
                        <span>Where are you going?</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Categories Row - Desktop -->
        <div class="row categories-row d-none d-md-block">
            <div class="col-12">
                <div class="d-flex">
                    <a href="#" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-map-marker-alt me-2"></i>Destination
                    </a>
                    <a href="{{ route('guidings.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-fish me-2"></i>Fishing Near Me
                    </a>
                    <a href="{{ route('blog.index') }}" class="me-4 text-white text-decoration-none">
                        <i class="fas fa-book-open me-2"></i>Magazine
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
                                <option value="">Select fish...</option>
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
.cag-header-navbar {
    background-color: #313041;
    padding-top: 16px;
    padding-bottom: 35px;
    position: relative;
    margin-bottom: 60px;
}

.cag-header-navbar .btn-outline-secondary {
    border: 1px solid rgba(255,255,255,0.3);
    background: transparent;
}

.cag-header-navbar .btn-outline-secondary:hover {
    background-color: rgba(255,255,255,0.1);
    border-color: white;
}

.cag-header-navbar .floating-search-container {
    position: absolute;
    left: 0;
    right: 0;
    bottom: -30px;
    /* transform: translateY(50%); */
    z-index: 1000;
}

.cag-header-navbar .search-box {
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cag-header-navbar .search-input {
    position: relative;
    margin-right: 12px;
}

.cag-header-navbar .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 1;
}

.cag-header-navbar .form-control,
.cag-header-navbar .form-select {
    height: 48px;
    padding-left: 40px;
    border: 1px solid #E85B40;
    border-radius: 4px;
}

.cag-header-navbar .form-control:focus,
.cag-header-navbar .form-select:focus {
    box-shadow: none;
    border-color: #E85B40;
}

.cag-header-navbar .search-button {
    background-color: #E85B40;
    color: white;
    border: none;
    height: 48px;
    padding: 0 24px;
    border-radius: 4px;
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

.cag-header-navbar .gap-4 {
    gap: 1rem !important;
}

.cag-header-navbar .row.mb-5,
.cag-header-navbar .row.mb-4,
.cag-header-navbar .row.mb-3 {
    margin-bottom: 0 !important;
}

.cag-header-navbar .categories-row {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Form controls alignment */
.cag-header-navbar .search-box .d-flex {
    align-items: center;
    gap: 12px;
}

.cag-header-navbar .search-input {
    position: relative;
    min-height: 48px;
    display: flex;
    align-items: center;
}

/* Consistent sizing for all form elements */
.cag-header-navbar .form-control,
.cag-header-navbar .form-select,
.cag-header-navbar select {
    height: 48px !important;
    line-height: 48px;
    padding: 0 40px;
    border: 1px solid #E85B40;
    border-radius: 4px;
    width: 100%;
}

/* Fix for select2 if you're using it */
.cag-header-navbar .select2-container {
    height: 48px !important;
}

.cag-header-navbar .select2-container .select2-selection--single {
    height: 48px !important;
    line-height: 48px;
    border: 1px solid #E85B40;
    border-radius: 4px;
}

.cag-header-navbar .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 48px;
    padding-left: 40px;
}

.cag-header-navbar .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 46px;
}

/* Search button alignment */
.cag-header-navbar .search-button {
    height: 48px;
    min-width: 120px;
    white-space: nowrap;
}

/* Input icons alignment */
.cag-header-navbar .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 1;
    pointer-events: none;
}

/* Fix for fish icon in select */
.cag-header-navbar .fa-fish.input-icon {
    z-index: 2;
}

/* Mobile Styles */
@media (max-width: 767px) {
    .cag-header-navbar {
        padding-bottom: 16px;
        margin-bottom: 0;
    }
    
    .mobile-profile-dropdown {
        position: relative;
    }
    
    .mobile-profile-dropdown .dropdown-menu {
        position: absolute;
        right: 0;
        left: auto;
        top: 100%;
        margin-top: 0.5rem;
        z-index: 1050; /* Higher z-index to prevent overlapping */
    }
    
    .search-summary {
        background: white;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        margin-bottom: 10px;
    }
    
    .categories-mobile {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .categories-mobile::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
    
    .mobile-profile-menu {
        width: 250px;
        max-width: calc(100vw - 2rem);
    }
}

/* Search Modal Styles */
#searchModal {
    z-index: 1055; /* Higher than dropdown */
}

/* Ensure dropdowns are above other content */
.dropdown-menu {
    z-index: 1040;
}

/* Hide desktop search on mobile */
@media (max-width: 767px) {
    .floating-search-container {
        display: none !important;
    }
}

/* Mobile Menu Styles */
.mobile-nav__toggler {
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mobile-nav__toggler:hover {
    color: rgba(255, 255, 255, 0.8) !important;
}

/* Ensure proper spacing between elements */
.mobile-profile-dropdown {
    margin-right: 12px;
}

/* Desktop-specific header styles */
.header-desktop-profile.dropdown {
    position: relative;
}

.header-desktop-profile .dropdown-toggle {
    padding: 8px 12px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.header-desktop-profile .dropdown-toggle:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.header-desktop-profile .dropdown-toggle::after {
    margin-left: 8px;
    vertical-align: middle;
}

.header-desktop-profile .header-profile-name {
    font-size: 14px;
    font-weight: 500;
    color: white;
}

.header-desktop-profile .dropdown-menu {
    min-width: 200px;
    margin-top: 8px;
    padding: 8px 0;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 4px;
}

.header-desktop-profile .dropdown-item {
    padding: 8px 16px;
    font-size: 14px;
    color: #333;
}

.header-desktop-profile .dropdown-item:hover {
    background-color: #f8f9fa;
}

.header-desktop-profile .dropdown-divider {
    margin: 4px 0;
}

.header-login-link {
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.2s;
}

.header-login-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white !important;
    text-decoration: none !important;
}

/* Ensure these styles only apply to desktop */
@media (min-width: 768px) {
    .header-desktop-profile .dropdown-toggle {
        display: flex;
        align-items: center;
    }
    
    .header-desktop-profile img {
        width: 32px;
        height: 32px;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
}

/* Preserve mobile styles */
@media (max-width: 767px) {
    .header-desktop-profile {
        display: none;
    }
}

/* Desktop Header Specific Styles */
@media (min-width: 768px) {
    .cag-header-navbar .nav-links {
        height: 45px;
    }
    
    .cag-header-navbar .nav-links a {
        font-size: 14px;
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    
    .cag-header-navbar .nav-links a:hover {
        background-color: rgba(255, 255, 255, 0.1);
        text-decoration: none;
    }
    
    .cag-header-navbar .btn-outline-light {
        border: 1px solid rgba(255, 255, 255, 0.5);
        padding: 8px 16px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .cag-header-navbar .btn-outline-light:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: white;
    }
    
    /* Adjust spacing between nav items */
    .cag-header-navbar .nav-links .gap-4 > * {
        margin-left: 1rem;
    }
    
    /* Language selector style */
    .cag-header-navbar .nav-links .fa-globe {
        margin-right: 4px;
    }
}

/* Desktop Header Styles */
@media (min-width: 768px) {
    .cag-header-navbar .top-nav-items {
        gap: 32px;
        height: 45px;
    }

    .cag-header-navbar .top-nav-items .nav-link {
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

    .cag-header-navbar .top-nav-items .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
    }

    /* Icons sizing */
    .cag-header-navbar .top-nav-items .nav-link i {
        font-size: 18px;
    }

    /* Language selector specific styling */
    .cag-header-navbar .language-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    /* Become a guide link */
    .cag-header-navbar .become-guide-link {
        font-weight: 500;
    }

    /* Profile section */
    .cag-header-navbar .header-desktop-profile {
        display: flex;
        align-items: center;
    }

    .cag-header-navbar .header-desktop-profile img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .cag-header-navbar .header-desktop-profile .dropdown-toggle {
        font-size: 16px;
        font-weight: 500;
    }

    /* Sign up button */
    .cag-header-navbar .signup-btn {
        border: 1.5px solid rgba(255, 255, 255, 0.5);
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .cag-header-navbar .signup-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: white;
    }

    /* Login link */
    .cag-header-navbar .login-link {
        font-weight: 500;
    }

    /* Ensure proper vertical alignment */
    .cag-header-navbar .logo,
    .cag-header-navbar .top-nav-items,
    .cag-header-navbar .top-nav-items > * {
        display: flex;
        align-items: center;
    }

    /* Profile dropdown */
    .cag-header-navbar .header-desktop-profile .dropdown-toggle::after {
        margin-left: 8px;
        border-top-width: 6px;
    }

    .cag-header-navbar .header-desktop-profile .dropdown-menu {
        margin-top: 8px;
        right: 0;
        left: auto;
        min-width: 200px;
        padding: 8px 0;
        font-size: 15px;
    }

    .cag-header-navbar .header-desktop-profile .dropdown-item {
        padding: 8px 16px;
    }
}

/* Preserve mobile responsiveness */
@media (max-width: 767px) {
    .cag-header-navbar .top-nav-items {
        display: none;
    }
}

/* Categories styling for both headers */
.categories-row a,
.categories-mobile a {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 50px;
    transition: background-color 0.2s;
    margin-right: 16px; /* Increased spacing between categories */
}

.categories-row a:hover,
.categories-mobile a:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

/* Mobile adjustments */
@media (max-width: 767px) {
    /* Logo sizing */
    .cag-header-navbar .logo img {
        height: 35px; /* Reduced height */
        width: auto; /* Maintain aspect ratio */
        object-fit: contain;
    }
    
    /* Header text adjustments */
    .header-contents {
        padding: 8px 0; /* Reduced padding */
    }
    
    .header-contents h1 {
        font-size: 22px;
        margin-top: 8px !important;
        margin-bottom: 4px;
    }
    
    .header-contents p {
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    /* Categories mobile spacing */
    .categories-mobile {
        margin: 8px 0; /* Reduced margin */
        padding: 0;
    }
    
    .categories-mobile a {
        margin-right: 8px; /* Reduced spacing between mobile categories */
        font-size: 14px;
        padding: 6px 12px; /* Slightly smaller padding for mobile */
    }
    
    /* Reduce bottom padding */
    .cag-header-navbar {
        padding-bottom: 12px;
    }
}

/* Desktop adjustments */
@media (min-width: 768px) {
    /* Increase space between categories and search */
    .categories-row {
        margin-top: 16px !important;
        margin-bottom: 32px !important; /* Increased space above search bar */
    }
    
    .categories-row a {
        margin-right: 24px; /* More space between desktop categories */
    }
    
    /* Adjust floating search container position */
    .floating-search-container {
        bottom: -35px; /* Slightly lower position */
    }
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
    // Get the specific elements
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