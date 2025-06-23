@extends('layouts.app-v2-1')

@section('css_after')
    <style>
        .profile-dashboard {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 40px 0 20px 0;
            margin-top: 20px;
        }

        .profile-sidebar {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .sidebar-wrapper {
            padding: 0;
        }

        .user-welcome-card {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .user-avatar {
            margin-bottom: 15px;
        }

        .avatar-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #313041;
        }

        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #313041, #252238);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .avatar-placeholder i {
            font-size: 30px;
            color: white;
        }

        .user-info h4 {
            margin: 0 0 8px 0;
            font-weight: 600;
            color: #333;
        }

        .user-type .badge {
            font-size: 0.85em;
            padding: 6px 12px;
        }

        .profile-nav {
            padding: 0;
        }

        .nav-section {
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 0;
        }

        .nav-section:last-child {
            border-bottom: none;
        }

        .nav-section-title {
            color: #666;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 20px 15px 20px;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #555;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: #f8f9fa;
            color: #313041;
            text-decoration: none;
            border-left-color: #313041;
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(49,48,65,0.1), rgba(49,48,65,0.05));
            color: #313041;
            border-left-color: #313041;
            font-weight: 600;
        }

        .nav-link.highlight {
            background: linear-gradient(135deg, #313041, #252238);
            color: white;
            border-left-color: #252238;
        }

        .nav-link.highlight:hover {
            background: linear-gradient(135deg, #252238, #1a1925);
            color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 14px;
        }

        .profile-main-content {
            padding-left: 20px;
        }

        .main-content-wrapper {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 30px;
            min-height: 600px;
        }

        .page-title {
            margin: 0 0 20px 0;
            color: #333;
            font-weight: 600;
            font-size: 1.75rem;
        }

        .page-subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .profile-main-content {
                padding-left: 0;
                margin-top: 20px;
            }
            
            .main-content-wrapper {
                padding: 20px;
            }
        }

        /* Legacy styles for backwards compatibility */
        .box {
            border: 1px solid lightgrey;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            padding: 25% 0;
            cursor: pointer;
            transition: all 0.4s 0s ease-in-out;
        }

        .box:hover {
            border-color: var(--thm-primary);
        }

        .box > i {
            font-size: 1.6em;
        }

        .box-title {
            margin-top: 5px;
        }

        .accordion-button {
            color: gray !important;
        }

        .accordion-button:not(.collapsed) {
            background-color: inherit;
            color: inherit;
        }

        .accordion-button:focus {
            z-index: 3;
            border-color: inherit;
            outline: 0;
            box-shadow: inherit;
        }

        .fa-chevron-right {
            font-size: 10px;
        }

        .accordion-body a:hover {
            color: var(--thm-primary);
        }

        .col-4 a:hover {
            color: var(--thm-primary);
        }
    </style>
@stop

@section('content')
    <div class="profile-dashboard">
        <div class="container profile-wrapper">
            <!--Page Header Start-->
            @if(!Request::routeIs('ratings.show') && !Request::routeIs('ratings.notified') && !Request::routeIs('ratings.review'))
                <div class="container mb-4">
                    <section class="page-header">
                        <div class="page-header__bottom breadcrumb-container">
                            <div class="page-header__bottom-inner">
                                <ul class="thm-breadcrumb list-unstyled">
                                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                                    @unless(Request::routeIs('profile.index'))
                                        <li><a href="{{route('profile.index')}}">@lang('profile.profile')</a></li>
                                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                                    @endunless
                                    @if(Request::routeIs('guidings.edit'))
                                        <li><a href="{{route('profile.myguidings')}}">{{translate('Meine Guidings')}}</a></li>
                                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                                    @endif
                                    <li class="active">@yield('title')</li>
                                </ul>
                            </div>
                        </div>
                    </section>
                </div>
            @endif
            <!--Page Header End-->

            <div class="row">
                <!-- Sidebar Navigation -->
                <div class="col-lg-3 col-md-4 profile-sidebar">
                    <div class="sidebar-wrapper">
                        <!-- User Welcome Section -->
                        <div class="user-welcome-card">
                            <a href="{{ route('profile.index') }}">
                                <div class="user-avatar">
                                    @if(Auth::user()->profil_image)
                                        <img src="{{ asset('images/' . Auth::user()->profil_image) }}" alt="Profile" class="avatar-img">
                                    @else
                                        <div class="avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="user-info">
                                    <h4>{{ Auth::user()->firstname ?? 'Welcome' }} {{ Auth::user()->lastname ?? 'Back' }}</h4>
                                    <p class="user-type">
                                        @if(Auth::user()->is_guide)
                                            <span class="badge badge-success"><i class="fas fa-fish"></i> Fishing Guide</span>
                                        @else
                                            <span class="badge badge-primary"><i class="fas fa-user"></i> Angler</span>
                                        @endif
                                    </p>
                                </div>
                            </a>
                        </div>

                        <!-- Navigation Menu -->
                        <nav class="profile-nav">
                            <div class="nav-section">
                                <ul class="nav-list">
                                    <li class="nav-item">
                                        <a href="{{ route('profile.index') }}" class="nav-link {{ Request::routeIs('profile.index') ? 'active' : '' }}">
                                            <i class="fas fa-home"></i>
                                            <span>Dashboard</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Profile Section -->
                            <div class="nav-section">
                                <h6 class="nav-section-title">Profile</h6>
                                <ul class="nav-list">
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings') }}" class="nav-link {{ Request::routeIs('profile.settings') ? 'active' : '' }}">
                                            <i class="fas fa-user-edit"></i>
                                            <span>Personal Details</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.password') }}" class="nav-link {{ Request::routeIs('profile.password') ? 'active' : '' }}">
                                            <i class="fas fa-lock"></i>
                                            <span>Password & Security</span>
                                        </a>
                                    </li>
                                    @if(!Auth::user()->is_guide)
                                    <li class="nav-item">
                                        <a href="{{ route('profile.becomeguide') }}" class="nav-link {{ Request::routeIs('profile.becomeguide') ? 'active' : '' }}">
                                            <i class="fas fa-certificate"></i>
                                            <span>Become a Guide</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Booking Section -->
                            <div class="nav-section">
                                <h6 class="nav-section-title">Bookings</h6>
                                <ul class="nav-list">
                                    <li class="nav-item">
                                        <a href="{{ route('profile.bookings') }}" class="nav-link {{ Request::routeIs('profile.bookings') || Request::routeIs('profile.showbooking') || Request::routeIs('profile.guidebookings') ? 'active' : '' }}">
                                            <i class="fas fa-calendar-check"></i>
                                            <span>All Bookings</span>
                                        </a>
                                    </li>
                                    @if(Auth::user()->is_guide)
                                    <li class="nav-item">
                                        <a href="{{ route('profile.calendar') }}" class="nav-link {{ Request::routeIs('profile.calendar') ? 'active' : '' }}">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Calendar</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>

                            @if(Auth::user()->is_guide)
                            <!-- My Guidings Section -->
                            <div class="nav-section">
                                <h6 class="nav-section-title">My Guidings</h6>
                                <ul class="nav-list">
                                    <li class="nav-item">
                                        <a href="{{ route('profile.myguidings') }}" class="nav-link {{ Request::routeIs('profile.myguidings') || Request::routeIs('guidings.edit') ? 'active' : '' }}">
                                            <i class="fas fa-list-alt"></i>
                                            <span>All Guidings</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.newguiding') }}" class="nav-link highlight {{ Request::routeIs('profile.newguiding') ? 'active' : '' }}">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Create New Guiding</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="col-lg-9 col-md-8 profile-main-content">
                    <div class="main-content-wrapper">
                        @unless(Request::routeIs('profile.index'))
                            {{-- <h2 class="page-title">@yield('title')</h2> --}}
                            @hasSection('page-subtitle')
                                <p class="page-subtitle">@yield('page-subtitle')</p>
                            @endif
                        @endunless
                        
                        @yield('profile-content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"
            integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous"
            async></script>
@endsection
