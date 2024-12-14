<nav class="navbar-custom mb-5" style="background-color: var(--thm-black); border-bottom: none!important;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-4 col-lg-4">
                <div class="logo">
                    <a href="{{ route('welcome') }}"><img src="{{ asset('assets/images/logo/CatchAGuide_Logo_PNG_All White.png') }}" alt="Logo"></a>
                </div>
            </div>
            <div class="col-8 col-lg-8 d-none d-lg-block">
                <div class="nav-links text-right d-flex justify-content-end align-items-end">
                <!-- <div class="nav-links text-right d-flex justify-content-end align-items-center"> -->
                    @if(Auth::check())
                        <a href="{{ route('profile.bookings') }}" class="me-3" style="color: #787780;">Bookings</a>
                        {{-- <a href="#" class="me-3" style="color: #787780;">Inbox</a> --}}
                        <a href="#" class="me-3" style="color: #787780;">Get Help</a>   
                        <div class="dropdown">
                            <a class="dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color: #787780;">
                                {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.index') }}" style="color: #787780;">Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.auth.logout') }}" style="color: #787780;">Logout</a></li>
                            </ul>
                        </div>
                    @else
                        <a href="#" class="button new-filter-btn me-3" style="color: #787780;">Become a guide</a>
                        <a class="header-login-link me-3" href="{{ route('login') }}" style="color: #fff!important;">Log in</a>
                        <a class="header-signup-link" href="{{ route('login') }}" style="color: #fff!important;">Sign up</a>
                    @endif
                </div>
            </div>
        </div>

        <!-- <form class="search-form-short row gx-2 pe-0 d-none d-sm-none d-md-none" id="global-search" action="{{route('guidings.index')}}" method="get"> -->
        @if(request()->segment(1) != 'guidings')
        <form class="search-form-short row gx-2 pe-0" id="global-search" action="{{route('guidings.index')}}" method="get">
            <div class="row global-search-row">
                <div class="col-lg-4 col-sm-12 column-input mx-0 pt-1 px-0">
                    <div class="form-group">
                        <div class="d-flex align-items-center small">
                            <i class="fa fa-search fa-fw text-muted position-absolute px-2"></i>
                            <input  id="searchPlace" name="place" type="text" class="form-control rounded-0 ps-4" placeholder="@lang('homepage.searchbar-destination')"  autocomplete="on">
                            <input type="hidden" id="placeLat" name="placeLat"/>
                            <input type="hidden" id="placeLng" name="placeLng"/>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-sm-12 column-input my-1 px-1">
                    <div class="form-group">
                        <div class="d-flex align-items-center small">
                            <i class="fa fa-user fa-fw text-muted position-absolute px-2"></i>
                            <input type="number" min="1" max="5" class="form-control rounded-0 ps-4" name="num_guests" placeholder="@lang('homepage.searchbar-person')" />
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-12 column-input my-1 px-0">
                    <div class="d-flex align-items-center small myselect2 p-0">
                        <i class="fa fa-fish fa-fw text-muted position-absolute px-2"></i>
                        <select class="form-control form-select rounded-0 ps-4" id="home_target_fish" name="target_fish[]" style="width:100%">
                            
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 my-1 px-0">
                    <button type="submit" class="form-control new-filter-btn">@lang('homepage.searchbar-search')</button>
                </div>
            </div>
        </form>
        @endif
    </div>
</nav>