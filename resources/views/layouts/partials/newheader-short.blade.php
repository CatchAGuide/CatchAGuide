<nav class="navbar-custom short-header {{ request()->is('/') ? 'with-bg' : '' }} {{ request()->is('guidings*') ? 'no-search' : '' }}">
    <div class="container">
        <!-- Top Row -->
        <div class="row align-items-center">
            <!-- Logo and Mobile Profile -->
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('welcome') }}">
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Logo" style="height: 45px;">
                    </a>
                </div>
                <!-- Mobile Icons -->
                <div class="d-flex align-items-center d-md-none">
                    <a href="#" class="text-white me-3"><i class="fas fa-bell"></i></a>
                    <div class="dropdown mobile-profile-dropdown">
                        <img src="{{ Auth::user()->avatar ?? asset('assets/images/default-avatar.png') }}" 
                             class="rounded-circle" 
                             style="width: 32px; height: 32px;" 
                             data-bs-toggle="dropdown"
                             alt="Profile">
                        <div class="dropdown-menu dropdown-menu-end mobile-profile-menu">
                            <div class="px-3 py-2">
                                <img src="{{ Auth::user()->avatar ?? asset('assets/images/default-avatar.png') }}" 
                                     class="rounded-circle me-2" 
                                     style="width: 40px; height: 40px;">
                                <span>{{ Auth::user()->firstname }}</span>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="fas fa-user me-2"></i> Manage account
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-globe me-2"></i> Language: EN
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

            <!-- Desktop Menu -->
            <div class="col-8 d-none d-md-block">
                <div class="nav-links d-flex justify-content-end align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <div class="dropdown">
                            <a href="#" class="text-white d-flex align-items-center" data-bs-toggle="dropdown">
                                <img src="{{ asset('assets/images/flags/en.png') }}" class="me-1" style="width: 20px; height: 15px;" alt="EN">
                                EN
                            </a>
                            <div class="dropdown-menu">
                                <!-- Add language options here -->
                            </div>
                        </div>
                        <a href="{{ route('additional.contact') }}" class="text-white"><i class="fas fa-question-circle"></i></a>
                        @if(Auth::check())
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                    <img src="{{ Auth::user()->avatar ?? asset('assets/images/default-avatar.png') }}" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px;" 
                                         alt="Profile">
                                    <span>{{ Auth::user()->firstname }}</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('profile.index') }}">Profile</a>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.auth.logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
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