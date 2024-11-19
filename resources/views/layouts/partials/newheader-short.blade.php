<nav class="navbar-custom">
    <div class="container">
        <!-- Top Row -->
        <div class="row align-items-center">
            <div class="col-4">
                <div class="logo">
                    <a href="{{ route('welcome') }}">
                        <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Logo" style="height: 45px;">
                    </a>
                </div>
            </div>
            <div class="col-8">
                <div class="nav-links d-flex justify-content-end align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <a href="#" class="text-white d-flex align-items-center">
                            <img src="{{ asset('assets/images/flags/en.png') }}" class="me-1" style="width: 20px; height: 15px;" alt="EN">
                            EN
                        </a>
                        <a href="{{ route('additional.contact') }}" class="text-white"><i class="fas fa-question-circle"></i></a>
                        @if(Auth::check())
                            <div class="d-flex align-items-center">
                                <a href="{{ route('profile.index') }}" class="d-flex align-items-center text-white text-decoration-none">
                                    <img src="{{ Auth::user()->avatar ?? asset('assets/images/default-avatar.png') }}" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px;" 
                                         alt="Profile">
                                    <span>{{ Auth::user()->firstname }}</span>
                                </a>
                            </div>
                        @else
                            <a href="#" class="text-white text-decoration-none">Become a guide</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary text-white px-4">Log in</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary text-white px-4">Sign up</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Row -->
        <div class="row categories-row">
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

    <!-- Search Row - Floating -->
    @if(request()->segment(1) != 'guidings')
    <div class="floating-search-container">
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
.navbar-custom {
    background-color: #313041;
    padding-top: 16px;
    padding-bottom: 35px;
    position: relative;
}

.btn-outline-secondary {
    border: 1px solid rgba(255,255,255,0.3);
    background: transparent;
}

.btn-outline-secondary:hover {
    background-color: rgba(255,255,255,0.1);
    border-color: white;
}

.floating-search-container {
    position: absolute;
    left: 0;
    right: 0;
    bottom: -30px;
    /* transform: translateY(50%); */
    z-index: 1000;
}

.search-box {
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.search-input {
    position: relative;
    margin-right: 12px;
}

.input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 1;
}

.form-control,
.form-select {
    height: 48px;
    padding-left: 40px;
    border: 1px solid #E85B40;
    border-radius: 4px;
}

.form-control:focus,
.form-select:focus {
    box-shadow: none;
    border-color: #E85B40;
}

.search-button {
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

.gap-4 {
    gap: 1rem !important;
}

.row.mb-5,
.row.mb-4,
.row.mb-3 {
    margin-bottom: 0 !important;
}

.categories-row {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
}
</style>

<script>
    var selectTarget = $('#home_target_fish');
    
</script>