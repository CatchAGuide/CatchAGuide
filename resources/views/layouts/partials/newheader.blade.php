
<nav class="navbar-custom container" style="backgorund-color:#fff;">
    <div class="logo">
        <a href="{{ route('welcome') }}"><img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="Logo"></a>
    </div>
    <div class="nav-links" style="color: #ccc;">
        <a href="#" class="button new-filter-btn">Become a guide</a>
        <a class="header-login-link" href="{{ route('login') }}">Log in</a>
        <a class="header-signup-link" href="{{ route('login') }}">Sign up</a>
        <div class="language-wrapper d-inline-block">
            <form action="{{ route('language.switch') }}" method="POST">
                @csrf
                <select name="language" class="selectpicker" data-width="fit" onchange="this.form.submit()">
                    @foreach (config('app.locales') as $key => $locale)
                    <option  value="{{ $locale }}" data-content='<span class="fi fi-{{$key}}"></span>' {{ app()->getLocale() == $locale ? 'selected' : '' }}></option>
                    @endforeach
                </select>        
            </form>
        </div>
    </div>
</nav>
<header class="header" style="background-image: url('{{ asset('assets/images/allguidings.jpg') }}'); background-size: cover; background-position: center;">
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