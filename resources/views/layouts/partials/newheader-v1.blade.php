<nav class="navbar-custom container" style="background-color:#fff; position: absolute; left: 10%; background-color: transparent;">
    <div class="logo">
        <a href="{{ route('welcome') }}"><img src="{{ asset('assets/images/logo/CatchAGuide_Logo_PNG_All White.png') }}" alt="Logo"></a>
    </div>
    <div class="nav-links d-none d-sm-flex align-items-center" style="color: #ccc;">
        @if(Auth::check())
            <a href="{{ route('profile.bookings') }}" class="button new-filter-btn me-3" style="color: #787780;">Bookings</a>
            <a href="#" class="me-3" style="color: #787780;">Get Help</a>   
            <div class="dropdown d-inline-block">
                <a class="dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color: #787780;">
                    {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                </a>
                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.index') }}" style="color: #787780;">Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.auth.logout') }}" style="color: #787780;">Logout</a></li>
                </ul>
            </div>
            <div class="language-wrapper d-inline-block ms-3">
                <form action="{{ route('language.switch') }}" method="POST">
                    @csrf
                    <select name="language" class="selectpicker" data-width="fit" onchange="this.form.submit()">
                        @foreach (config('app.locales') as $key => $locale)
                        <option  value="{{ $locale }}" data-content='<span class="fi fi-{{$key}}"></span>' {{ app()->getLocale() == $locale ? 'selected' : '' }}></option>
                        @endforeach
                    </select>        
                </form>
            </div>
        @else
            <a href="#" class="button new-filter-btn me-3" style="color: #787780;">Become a guide</a>
            <a class="header-login-link me-3" href="{{ route('login') }}" style="color: #fff!important;">Log in</a>
            <a class="header-signup-link" href="{{ route('login') }}" style="color: #fff!important;">Sign up</a>
        @endif
    </div>
    <div class="d-flex justify-content-between align-items-center d-block d-sm-none">
        <a href="#" class="mobile-nav__toggler" style="padding-top: 15px; padding-bottom: 15px;"><i class="fa fa-bars"></i></a>
    </div>
</nav>
<header class="header" style="background-image: url('{{ asset('assets/images/allguidings.jpg') }}'); background-size: cover; background-position: center; z-index: 0; height: 320px; padding-top: 80px;">
    <div class="overlay"></div>
    <div class="header-content container">
        <h1 class="h2 mt-5">@yield('header_title')</h1>
        <p>@yield('header_sub_title')</p>
    </div>
        
    <form class="search-form row gx-2 pe-0" id="global-search" action="{{route('guidings.index')}}" method="get">
        <div class="row" style="padding-right: 0;">
            <div class="col-lg-4 column-input mx-0 pt-1 px-1">
                <div class="form-group">
                    <div class="d-flex align-items-center small">
                        <i class="fa fa-search fa-fw text-muted position-absolute px-2"></i>
                        <input  id="searchPlace" name="place" type="text" class="form-control rounded-0 ps-4" placeholder="@lang('homepage.searchbar-destination')"  autocomplete="on">
                        <input type="hidden" id="placeLat" name="placeLat"/>
                        <input type="hidden" id="placeLng" name="placeLng"/>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 column-input my-1 px-1">
                <div class="form-group">
                    <div class="d-flex align-items-center small">
                        <i class="fa fa-user fa-fw text-muted position-absolute px-2"></i>
                        <input type="number" min="1" max="5" class="form-control rounded-0 ps-4" name="num_guests" placeholder="@lang('homepage.searchbar-person')" />
                    </div>
                </div>
            </div>
            <div class="col-lg-4 column-input my-1 px-1">
                <div class="d-flex align-items-center small myselect2 p-0">
                    <i class="fa fa-fish fa-fw text-muted position-absolute px-2"></i>
                    <select class="form-control form-select rounded-0 ps-4" id="home_target_fish" name="target_fish[]" style="width:100%">
                        
                    </select>
                </div>
            </div>
            <div class="col-lg-2 my-1 px-1">
                <button type="submit" class="form-control new-filter-btn">@lang('homepage.searchbar-search')</button>
            </div>
        </div>
    </form>
</header>