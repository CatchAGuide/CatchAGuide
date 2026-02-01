<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('welcome') }}">
                <img src="{{ asset('assets/images/logo.png') }}" class="header-brand-img desktop-logo" alt="logo">
                <img src="{{ asset('assets/images/logo.png') }}" class="header-brand-img toggle-logo" alt="logo">
                <img src="{{ asset('assets/images/logo.png') }}" class="header-brand-img light-logo" alt="logo">
                <img src="{{ asset('assets/images/logo.png') }}" class="header-brand-img light-logo1" alt="logo">
            </a>
            <!-- LOGO -->
        </div>
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"/></svg></div>
            <ul class="side-menu">
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.index') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.index') }}"><i class="side-menu__icon fe fe-home"></i><span class="side-menu__label">Dashboard</span></a>
                </li>

                <li class="sub-category">
                    <h3>Administration</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.customers.index') }}"><i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">Customers</span></a>
                </li>
                <li class="slide {{ request()->routeIs('admin.guides.*') || request()->routeIs('admin.guide-requests.*') || request()->routeIs('admin.guide-analytics.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.guides.*') || request()->routeIs('admin.guide-requests.*') || request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#"><i class="side-menu__icon fe fe-anchor"></i><span class="side-menu__label">Guides & Tours</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.guides.index') }}" class="slide-item {{ request()->routeIs('admin.guides.index') && !request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}">Guides</a></li>
                        <li><a href="{{ route('admin.guides.index') }}" class="slide-item">Providers</a></li>
                        <li><a href="{{ route('admin.guide-requests.index') }}" class="slide-item {{ request()->routeIs('admin.guide-requests.index') ? 'active' : '' }}">Guide Requests</a></li>
                        <li><a href="{{ route('admin.guide-analytics.index') }}" class="slide-item {{ request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}">Guide Analytics</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.guidings.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.guidings.index') }}"><i class="side-menu__icon fe fe-briefcase"></i><span class="side-menu__label">Guidings</span></a>
                    <a class="side-menu__item {{ request()->routeIs('admin.rental-boats.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.rental-boats.index') }}"><i class="side-menu__icon fas fa-ship"></i><span class="side-menu__label">Rental Boats</span></a>
                    <a class="side-menu__item {{ request()->routeIs('admin.accommodations.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.accommodations.index') }}"><i class="side-menu__icon fas fa-hotel"></i><span class="side-menu__label">Accommodations</span></a>
                    <a class="side-menu__item {{ request()->routeIs('admin.special-offers.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.special-offers.index') }}"><i class="side-menu__icon fas fa-gift"></i><span class="side-menu__label">Special Offers</span></a>
                    <a class="side-menu__item {{ request()->routeIs('admin.camps.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.camps.index') }}"><i class="side-menu__icon fas fa-campground"></i><span class="side-menu__label">Camps</span></a>
                    <a class="side-menu__item {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.vacations.index') }}"><i class="side-menu__icon fe fe-book-open"></i><span class="side-menu__label">Vacations (Old)</span></a>
                    
                    <a class="side-menu__item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#"><i class="side-menu__icon fe fe-dollar-sign"></i><span class="side-menu__label">Bookings</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.bookings.index') }}" class="slide-item  {{ request()->routeIs('admin.vacations.index') ? 'active' : '' }}"> Guidings</a></li>
                        <li><a href="{{ route('admin.vacations.bookings') }}" class="slide-item  {{ request()->routeIs('admin.vacations.bookings') ? 'active' : '' }} text-secondary"> Vacations</a></li>
                    </ul>
                    {{-- <a class="side-menu__item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.payments.index') }}"><i class="side-menu__icon fe fe-credit-card"></i><span class="side-menu__label">Zahlungen</span></a> --}}
                </li>

                <li class="sub-category">
                    <h3>System</h3>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.contact-requests.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.contact-requests.index') }}"><i class="side-menu__icon fe fe-mail"></i><span class="side-menu__label">Contact Request</span></a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.email-logs.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.email-logs.index') }}"><i class="side-menu__icon fe fe-mail"></i><span class="side-menu__label">Email Logs</span></a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.employees.index') }}"><i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">Employees</span></a>
                </li>

                <li class="slide {{ request()->routeIs('admin.settings.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}  "
                       data-bs-toggle="slide"
                       href="">
                        <i class="side-menu__icon fe fe-settings"></i>
                        <span class="side-menu__label">Guiding Settings</span>
                    </a>
                    <ul class="slide-menu ">
                        <li><a href="{{route('admin.settings.levelindex')}}" class="slide-item  {{ request()->routeIs('admin.settings.levelindex') ? 'active' : '' }}" class="slide-item">Fishing Level</a></li>
                        <li><a href="{{route('admin.settings.fishingtypeindex')}}" class="slide-item  {{ request()->routeIs('admin.settings.fishingtypeindex') ? 'active' : '' }}" class="slide-item">Fishing Type</a></li>
                        <li><a href="{{route('admin.settings.equipmentindex')}}" class="slide-item " class="slide-item">Fishing Equipment</a></li>
                        <li><a href="{{route('admin.settings.fishingfromindex')}}" class="slide-item  {{ request()->routeIs('admin.settings.fishingfromindex') ? 'active' : '' }}" class="slide-item">Fishing From</a></li>
                        <li><a href="{{route('admin.settings.inclussionindex')}}" class="slide-item  {{ request()->routeIs('admin.settings.inclussionindex') ? 'active' : '' }}" class="slide-item">Included</a></li>
                        <li><a href="{{route('admin.settings.methodindex')}}" class="slide-item  {{ request()->routeIs('admin.settings.methodindex') ? 'active' : '' }}" class="slide-item">Method</a></li>
                        <li><a href="{{route('admin.settings.waterindex')}}" class="slide-item  {{ request()->routeIs('admin.settings.waterindex') ? 'active' : '' }}" class="slide-item">Water Types</a></li>
                        <li><a href="{{route('admin.settings.targetindex')}}" class="slide-item  {{ request()->routeIs('admin.settings.targetindex') ? 'active' : '' }}"> Target Fish</a></li>
                        <li><a href="{{route('admin.settings.emailmaintenance')}}" class="slide-item  {{ request()->routeIs('admin.settings.emailmaintenance') ? 'active' : '' }}"> Email Maintenance</a></li>
                    </ul>
                </li>

                {{-- <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.faq.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.faq.index') }}"><i class="side-menu__icon fe fe-help-circle"></i><span class="side-menu__label">Faq's</span></a>
                </li> --}}
                <li class="slide {{ request()->routeIs('admin.faq.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.faq.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#"><i class="side-menu__icon fe fe-book-open"></i><span class="side-menu__label">Faq</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.faq.home') }}" class="slide-item  {{ request()->routeIs('admin.faq.home') ? 'active' : '' }}">Home</a></li>
                        <li><a href="{{ route('admin.faq.searchrequest') }}" class="slide-item  {{ request()->routeIs('admin.faq.search-request') ? 'active' : '' }}">Search Request</a></li>
                    </ul>
                </li>
                <li class="slide {{ request()->routeIs('admin.blog.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#"><i class="side-menu__icon fe fe-book-open"></i><span class="side-menu__label">Blog</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.blog.threads.index') }}" class="slide-item  {{ request()->routeIs('admin.blog.threads.*') ? 'active' : '' }}"> Posts</a></li>
                        <li><a href="{{ route('admin.blog.categories.index') }}" class="slide-item  {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}"> Categories</a></li>
                    </ul>
                </li>
                <a class="side-menu__item {{ request()->routeIs('admin.newblog.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#"><i class="side-menu__icon fe fe-book-open"></i><span class="side-menu__label">Category</span><i class="angle fe fe-chevron-right"></i></a>
                <ul class="slide-menu">
                    <li><a href="{{ route('admin.category.target-fish.index') }}" class="slide-item  {{ request()->routeIs('admin.category.target-fish.*') ? 'active' : '' }}"> Target Fish</a></li>
                    <li><a href="{{ route('admin.category.methods.index') }}" class="slide-item  {{ request()->routeIs('admin.category.methods.*') ? 'active' : '' }}"> Methods</a></li>
                    <li><a href="{{ route('admin.newblog.threads.index') }}" class="slide-item  {{ request()->routeIs('admin.newblog.threads.*') ? 'active' : '' }}"> Posts</a></li>
                    <li><a href="{{ route('admin.category.country.index') }}" class="slide-item  {{ request()->routeIs('admin.category.country.*') ? 'active' : '' }}"> Country</a></li>
                    <li><a href="{{ route('admin.category.vacation-country.index') }}" class="slide-item  {{ request()->routeIs('admin.category.vacation-country.*') ? 'active' : '' }}"> Vacation Country</a></li>
                    <li><a href="{{ route('admin.category.region.index') }}" class="slide-item  {{ request()->routeIs('admin.category.region.*') ? 'active' : '' }}"> Region</a></li>
                    <li><a href="{{ route('admin.category.city.index') }}" class="slide-item  {{ request()->routeIs('admin.category.city.*') ? 'active' : '' }}"> City</a></li>
                </ul>
            </li>
                <li class="slide {{ request()->routeIs('admin.page-attribute.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.page-attribute.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#"><i class="side-menu__icon fe fe-book-open"></i><span class="side-menu__label">Page Attribute</span><i class="angle fe fe-chevron-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.page-attribute.en') }}" class="slide-item  {{ request()->routeIs('admin.page-attribute.en') ? 'active' : '' }}"> Catchaguide.com</a></li>
                        <li><a href="{{ route('admin.page-attribute.de') }}" class="slide-item  {{ request()->routeIs('admin.page-attribute.de') ? 'active' : '' }}"> Catchaguide.de</a></li>
                    </ul>
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/></svg></div>
        </div>
    </div>
    <!--/APP-SIDEBAR-->
</div>
