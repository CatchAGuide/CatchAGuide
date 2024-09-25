
<nav class="navbar-custom mb-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="logo">
                    <a href="{{ route('welcome') }}"><img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Logo"></a>
                </div>
            </div>
            <div class="col-lg-4">
                @if(Auth::check())
                <div class="nav-links text-right" style="text-align: right;">
                    <a href="#" class="button new-filter-btn">Become a guide</a>
                    <a class="header-login-link" href="{{ route('login') }}">Log in</a>
                    <a class="header-signup-link" href="{{ route('login') }}">Sign up</a>
                </div>
                @endif
            </div>
        </div>

        <form class="search-form-short row gx-2 pe-0" id="global-search" action="{{route('guidings.index')}}" method="get">
            <div class="row global-search-row">
                <div class="col-lg-4 column-input mx-0 pt-1 px-0">
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
                <div class="col-lg-4 column-input my-1 px-0">
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
    </div>
</nav>