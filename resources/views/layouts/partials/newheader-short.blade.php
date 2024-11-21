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
                        <a href="#" class="text-white me-3"><i class="fas fa-bell"></i></a>
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
                    <span>Where are you going?</span>
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
                            <input type="text" class="form-control" name="place" placeholder="@lang('homepage.searchbar-destination')">
                            <input type="hidden" id="placeLat" name="placeLat"/>
                            <input type="hidden" id="placeLng" name="placeLng"/>
                        </div>
                        <div class="search-input" style="width: 200px;">
                            <i class="fa fa-user input-icon"></i>
                            <input type="number" class="form-control" name="num_guests" placeholder="@lang('homepage.searchbar-person')">
                        </div>
                        <div class="search-input" style="width: 300px;">
                            <i class="fa fa-fish input-icon"></i>
                            <select class="form-select" name="target_fish[]" id="target_fish_search" >
                                <option value="">Select fish...</option>
                                @foreach(targets()::getAllTargets() as $target)
                                    <option value="{{$target['id']}}">
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
    padding-top: 16px;
    padding-bottom: 35px;
    position: relative;
    margin-bottom: 60px;
}

.short-header .btn-outline-secondary {
    border: 1px solid rgba(255,255,255,0.3);
    background: transparent;
}

.short-header .btn-outline-secondary:hover {
    background-color: rgba(255,255,255,0.1);
    border-color: white;
}

.short-header .floating-search-container {
    position: absolute;
    left: 0;
    right: 0;
    bottom: -30px;
    /* transform: translateY(50%); */
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
}

.short-header .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 1;
}

.short-header .form-control,
.short-header .form-select {
    height: 48px;
    padding-left: 40px;
    border: 1px solid #E85B40;
    border-radius: 4px;
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

.short-header .gap-4 {
    gap: 1rem !important;
}

.short-header .row.mb-5,
.short-header .row.mb-4,
.short-header .row.mb-3 {
    margin-bottom: 0 !important;
}

.short-header .categories-row {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Form controls alignment */
.short-header .search-box .d-flex {
    align-items: center;
    gap: 12px;
}

.short-header .search-input {
    position: relative;
    min-height: 48px;
    display: flex;
    align-items: center;
}

/* Consistent sizing for all form elements */
.short-header .form-control,
.short-header .form-select,
.short-header select {
    height: 48px !important;
    line-height: 48px;
    padding: 0 40px;
    border: 1px solid #E85B40;
    border-radius: 4px;
    width: 100%;
}

/* Fix for select2 if you're using it */
.short-header .select2-container {
    height: 48px !important;
}

.short-header .select2-container .select2-selection--single {
    height: 48px !important;
    line-height: 48px;
    border: 1px solid #E85B40;
    border-radius: 4px;
}

.short-header .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 48px;
    padding-left: 40px;
}

.short-header .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 46px;
}

/* Search button alignment */
.short-header .search-button {
    height: 48px;
    min-width: 120px;
    white-space: nowrap;
}

/* Input icons alignment */
.short-header .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 1;
    pointer-events: none;
}

/* Fix for fish icon in select */
.short-header .fa-fish.input-icon {
    z-index: 2;
}

/* Mobile Styles */
@media (max-width: 767px) {
    .short-header.navbar-custom {
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
    .short-header .nav-links {
        height: 45px;
    }
    
    .short-header .nav-links a {
        font-size: 14px;
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    
    .short-header .nav-links a:hover {
        background-color: rgba(255, 255, 255, 0.1);
        text-decoration: none;
    }
    
    .short-header .btn-outline-light {
        border: 1px solid rgba(255, 255, 255, 0.5);
        padding: 8px 16px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .short-header .btn-outline-light:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: white;
    }
    
    /* Adjust spacing between nav items */
    .short-header .nav-links .gap-4 > * {
        margin-left: 1rem;
    }
    
    /* Language selector style */
    .short-header .nav-links .fa-globe {
        margin-right: 4px;
    }
}

/* Desktop Header Styles */
@media (min-width: 768px) {
    .short-header .top-nav-items {
        gap: 32px;
        height: 45px;
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
        text-decoration: none;
    }

    /* Icons sizing */
    .short-header .top-nav-items .nav-link i {
        font-size: 18px;
    }

    /* Language selector specific styling */
    .short-header .language-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    /* Become a guide link */
    .short-header .become-guide-link {
        font-weight: 500;
    }

    /* Profile section */
    .short-header .header-desktop-profile {
        display: flex;
        align-items: center;
    }

    .short-header .header-desktop-profile img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .short-header .header-desktop-profile .dropdown-toggle {
        font-size: 16px;
        font-weight: 500;
    }

    /* Sign up button */
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

    /* Login link */
    .short-header .login-link {
        font-weight: 500;
    }

    /* Ensure proper vertical alignment */
    .short-header .logo,
    .short-header .top-nav-items,
    .short-header .top-nav-items > * {
        display: flex;
        align-items: center;
    }

    /* Profile dropdown */
    .short-header .header-desktop-profile .dropdown-toggle::after {
        margin-left: 8px;
        border-top-width: 6px;
    }

    .short-header .header-desktop-profile .dropdown-menu {
        margin-top: 8px;
        right: 0;
        left: auto;
        min-width: 200px;
        padding: 8px 0;
        font-size: 15px;
    }

    .short-header .header-desktop-profile .dropdown-item {
        padding: 8px 16px;
    }
}

/* Preserve mobile responsiveness */
@media (max-width: 767px) {
    .short-header .top-nav-items {
        display: none;
    }
}
</style>

<script>
    var selectTarget = $('#home_target_fish');
    
</script>

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
                                   placeholder="@lang('homepage.searchbar-destination')">
                            <input type="hidden" name="placeLat"/>
                            <input type="hidden" name="placeLng"/>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Number of Persons</label>
                        <div class="position-relative">
                            <i class="fas fa-user position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                            <input type="number" 
                                   class="form-control ps-5" 
                                   name="num_guests" 
                                   placeholder="@lang('homepage.searchbar-person')">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Target Fish</label>
                        <div class="position-relative">
                            <i class="fas fa-fish position-absolute top-50 translate-middle-y" style="left: 15px;"></i>
                            <select class="form-select ps-5" name="target_fish[]">
                                <option value="">Select fish...</option>
                                @foreach(targets()::getAllTargets() as $target)
                                    <option value="{{$target['id']}}">{{$target['name']}}</option>
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