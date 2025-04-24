
<header class="main-header clearfix">
    <div class="main-header__top">
        <div class="container">
            <div class="main-header__top-inner clearfix">
                <div class="main-header__top-left">
                    <ul class="list-unstyled main-header__top-address">
                        <li>
                            <div class="icon">
                                <span class="icon-phone-call"></span>
                            </div>
                            <div class="text">
                                <a href="tel:+49{{env('CONTACT_NUM')}}">+49 (0) {{env('CONTACT_NUM')}}</a>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <span class="icon-at"></span>
                            </div>
                            <div class="text">
                                <a href="mailto:info.catchaguide@gmail.com">info.catchaguide@gmail.com</a>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="main-header__top-right">
                    <div class="main-header__top-right-inner">
                        <div class="main-header__top-right-social">
                            <a href="https://www.facebook.com/CatchAGuide"><i class="fab fa-facebook"></i></a>
                            <a href="https://wa.me/+49{{env('CONTACT_NUM')}}"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://www.instagram.com/catchaguide_official/"><i class="fab fa-instagram"></i></a>
                        </div>
                        <div class="main-header__top-right-btn-box">
                            @if(Auth::check() && !Auth::user()->is_guide)
                                <a href="{{route('profile.becomeguide')}}" class="thm-btn main-header__top-right-btn">@lang('message.TagLine')</a>
                            @elseif(Auth::check() && Auth::user()->is_guide)
                                <a href="{{route('profile.index')}}" class="thm-btn main-header__top-right-btn">@lang('message.myaccount')</a>
                            @else
                                <a href="{{route('login')}}" class="thm-btn main-header__top-right-btn">@lang('message.TagLine')</a>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <nav class="main-menu clearfix">
        <div class="main-menu-wrapper clearfix">
            <div class="container clearfix">
                <div class="main-menu-wrapper-inner clearfix">
                    <div class="main-menu-wrapper__left clearfix">
                        <div class="main-menu-wrapper__main-menu">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('welcome') }}"><img src="{{ asset('assets/images/logo_mobil.jpg') }}" alt="" width="50%" height="auto" /></a>
                                <a href="#" class="mobile-nav__toggler" style="padding-top: 15px; padding-bottom: 15px;"><i class="fa fa-bars"></i></a>
    
                            </div>

                            <ul class="main-menu__list">
                                <?php /*
                                <li>
                                    <a href="{{ route('welcome') }}"><img src="{{ asset('assets/images/logo.png') }}" alt=""></a>
                                </li>
                                */ ?>
                                <li class="dropdown {{ request()->routeIs('welcome') ? 'current' : '' }}">
                                    <a href="{{ route('welcome') }}">@lang('message.home')</a>
                                </li>
                                <li class="dropdown {{ request()->routeIs('guidings.*') ? 'current' : '' }}">
                                    <a href="{{ route('guidings.index') }}">@lang('message.Guiding')</a>
                                </li>
                                <li class="dropdown {{ request()->routeIs('blog.*') ? 'current' : '' }}">
                                    <a href="{{ route($blogPrefix.'.index') }}">@lang('message.Magazine')</a>
                                </li>
                                @if($agent->ismobile())
                                    <li class="dropdown">
                                        @if(auth()->check())
                                            <a href="{{route('profile.index')}}">Mein Konto</a>
                                        @else
                                            <a href="{{route('profile.index')}}">@lang('message.loginRegister')</a>
                                        @endif
                                    </li>
                                @endif

                                @if(!$agent->isMobile())
                                    <li class="magazine-nmb {{ request()->routeIs('blog.*') ? 'current' : '' }}">
                                        <a href="{{ route($blogPrefix.'.index') }}">@lang('message.Magazine')</a>
                                    </li>
                                @endif



                                <nav class="main-menu-wrapper__nav d-flex`">
                                    @if(auth()->check())
                                        <div class="main-menu-wrapper__user" style="font-size: 15px">
                                            <img style="vertical-align: sub; margin-right: 5px;" src="{{asset('assets/images/icons/hello-hand.svg')}}" height="20" width="20" alt=""/>
                                            Hi, {{auth()->user()->firstname}}  
                                        </div>
                                        <ul class="main-menu-wrapper__right">
                                            
                                            <li class="dropdown {{ request()->routeIs('profile.*') ? 'current' : '' }}  ">
                                                <a href="{{route('profile.index')}}" class="main-menu__user icon-avatar"></a>
                                                <ul style="left: -160px !important;">
                                                    <li><a href="{{route('profile.settings')}}">@lang('message.myaccount')</a></li>

                                                    <li><a href="{{route('profile.bookings')}}">@lang('message.book-by-me')</a></li>
                                                    <li><a href="{{route('profile.favoriteguides')}}">@lang('message.favorite-guidings') ({{ auth()->user()->wishlist_items()->count() }})</a></li>
                                                    {{--<li><a href="{{route('profile.payments')}}">Zahlungsdetails</a></li>--}}

                                                    @if(auth()->user()->is_guide)
                                                        <li><a href="{{route('profile.calendar')}}">@lang('message.calendar')</a></li>
                                                        <li><a href="{{route('profile.myguidings')}}">@lang('message.my-guiding')</a></li>
                                                        <li><a href="{{ route('profile.guidebookings') }}">@lang('profile.bookedWithMe')</a></li>
                                                        <li><a href="{{route('profile.newguiding')}}">@lang('profile.creategiud')</a></li>
                                                    @else
                                                        <li><a href="{{route('profile.becomeguide')}}">@lang('message.verify-guide')</a></li>
                                                    @endif
                                                    <li><a href="{{route('chat')}}">@lang('message.my-news') ({{Auth::user()->countunreadmessages()}})</a></li>
                                                    <li><a href="javascript:void(0)" onclick="$('#logoutForm').submit();">@lang('message.logout')</a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <form action="{{ route('logout') }}" id="logoutForm" method="POST">
                                            @csrf
                                        </form>
                                    @else
                                        <a class="main-menu-wrapper__user sm-hidden" href="{{route('profile.index')}}" title="Einloggen/Registrieren"> 
                                            <span style="margin-right: 20px;">@lang('message.loginRegister')</span>
                                            <div class="main-menu-wrapper__right main-menu__user icon-avatar"></div>
                                        </a>
                                    @endif
                                    @if(!$agent->isMobile())
                                    <div class="language-wrapper">
                                        <form action="{{ route('language.switch') }}" method="POST">
                                            @csrf
                                            <select name="language" class="selectpicker" data-width="fit" onchange="this.form.submit()">
                                                @foreach (config('app.locales') as $key => $locale)
                                                <option  value="{{ $locale }}" data-content='<span class="fi fi-{{$key}}"></span>' {{ app()->getLocale() == $locale ? 'selected' : '' }}></option>
                                                @endforeach
                                            </select>        
                                        </form>
                                    </div>
                                    @endif
                                </nav>
                                    
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </nav>
</header>

<div class="stricky-header stricked-menu main-menu">
    <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
</div><!-- /.stricky-header -->
