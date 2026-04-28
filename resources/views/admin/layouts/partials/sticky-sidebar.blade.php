<!--APP-SIDEBAR-->
@php
    $listingsActive = request()->routeIs(
        'admin.guidings.*',
        'admin.rental-boats.*',
        'admin.accommodations.*',
        'admin.special-offers.*',
        'admin.camps.*',
        'admin.trips.*',
        'admin.listings.consolidated.*'
    );
@endphp
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('welcome') }}">
                <img src="{{ asset('assets/images/logo/CatchAGuide_Logo_PNG.png') }}" class="header-brand-img desktop-logo" alt="{{ config('app.name') }}">
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
                    <h3>Operations</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.customers.index') }}"><i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">Customers</span></a>
                </li>
                <li class="slide {{ request()->routeIs('admin.bookings.*') || request()->routeIs('admin.vacations.bookings') || request()->routeIs('admin.camp-vacation-bookings.*') || request()->routeIs('admin.trip-bookings.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.bookings.*') || request()->routeIs('admin.vacations.bookings') || request()->routeIs('admin.camp-vacation-bookings.*') || request()->routeIs('admin.trip-bookings.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-calendar"></i>
                        <span class="side-menu__label">Bookings</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.bookings.index') }}" class="slide-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">Guidings</a></li>
                        <li><a href="{{ route('admin.camp-vacation-bookings.index') }}" class="slide-item {{ request()->routeIs('admin.camp-vacation-bookings.*') ? 'active' : '' }}">Camps / vacations</a></li>
                        <li><a href="{{ route('admin.trip-bookings.index') }}" class="slide-item {{ request()->routeIs('admin.trip-bookings.*') ? 'active' : '' }}">Trips</a></li>
                    </ul>
                </li>
                <li class="slide {{ request()->routeIs('admin.finance.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-trending-up"></i>
                        <span class="side-menu__label">Finance</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.finance.analytics') }}" class="slide-item {{ request()->routeIs('admin.finance.analytics') ? 'active' : '' }}">Analytics</a></li>
                        <li><a href="{{ route('admin.finance.invoices') }}" class="slide-item {{ request()->routeIs('admin.finance.invoices') ? 'active' : '' }}">Invoices</a></li>
                    </ul>
                </li>

                <li class="slide {{ request()->routeIs('admin.strategy.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.strategy.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-bar-chart-2"></i>
                        <span class="side-menu__label">Strategy</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.strategy.index') }}" class="slide-item {{ request()->routeIs('admin.strategy.index') ? 'active' : '' }}">Overview</a></li>
                        <li><a href="{{ route('admin.strategy.supply-gaps') }}" class="slide-item {{ request()->routeIs('admin.strategy.supply-gaps') ? 'active' : '' }}">Supply gaps</a></li>
                        <li><a href="{{ route('admin.strategy.content-coverage') }}" class="slide-item {{ request()->routeIs('admin.strategy.content-coverage') ? 'active' : '' }}">Content coverage</a></li>
                    </ul>
                </li>

                <li class="sub-category">
                    <h3>Partners &amp; catalog</h3>
                </li>
                <li class="slide {{ request()->routeIs('admin.guides.*') || request()->routeIs('admin.guide-requests.*') || request()->routeIs('admin.guide-analytics.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.guides.*') || request()->routeIs('admin.guide-requests.*') || request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-anchor"></i>
                        <span class="side-menu__label">Guides &amp; tours</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.guides.index') }}" class="slide-item {{ request()->routeIs('admin.guides.index') && !request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}">Guides</a></li>
                        <li><a href="{{ route('admin.guides.index') }}" class="slide-item">Providers</a></li>
                        <li><a href="{{ route('admin.guide-requests.index') }}" class="slide-item {{ request()->routeIs('admin.guide-requests.index') ? 'active' : '' }}">Guide requests</a></li>
                        <li><a href="{{ route('admin.guide-analytics.index') }}" class="slide-item {{ request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}">Guide analytics</a></li>
                    </ul>
                </li>
                <li class="slide {{ $listingsActive ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ $listingsActive ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-package"></i>
                        <span class="side-menu__label">Listings</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.listings.consolidated.index') }}" class="slide-item {{ request()->routeIs('admin.listings.consolidated.*') ? 'active' : '' }}">All listings</a></li>
                        <li><a href="{{ route('admin.guidings.index') }}" class="slide-item {{ request()->routeIs('admin.guidings.*') ? 'active' : '' }}">Guidings</a></li>
                        <li><a href="{{ route('admin.rental-boats.index') }}" class="slide-item {{ request()->routeIs('admin.rental-boats.*') ? 'active' : '' }}">Rental boats</a></li>
                        <li><a href="{{ route('admin.accommodations.index') }}" class="slide-item {{ request()->routeIs('admin.accommodations.*') ? 'active' : '' }}">Accommodations</a></li>
                        <li><a href="{{ route('admin.special-offers.index') }}" class="slide-item {{ request()->routeIs('admin.special-offers.*') ? 'active' : '' }}">Special offers</a></li>
                        <li><a href="{{ route('admin.camps.index') }}" class="slide-item {{ request()->routeIs('admin.camps.*') ? 'active' : '' }}">Camps</a></li>
                        <li><a href="{{ route('admin.trips.index') }}" class="slide-item {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}">Trips</a></li>
                    </ul>
                </li>

                <li class="sub-category">
                    <h3>Communications</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.contact-requests.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.contact-requests.index') }}"><i class="side-menu__icon fe fe-inbox"></i><span class="side-menu__label">Contact requests</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.offer-sendout.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.offer-sendout.index') }}"><i class="side-menu__icon fe fe-send"></i><span class="side-menu__label">Custom camp offers</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.email-logs.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.email-logs.index') }}"><i class="side-menu__icon fe fe-mail"></i><span class="side-menu__label">Email logs</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                        <i class="side-menu__icon fe fe-bell"></i>
                        <span class="side-menu__label">Notifications</span>
                        @if(($adminNotificationCount ?? 0) > 0)
                            <span class="side-menu__counter">{{ $adminNotificationCount > 9 ? '9+' : $adminNotificationCount }}</span>
                        @endif
                    </a>
                </li>

                <li class="sub-category">
                    <h3>Team</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.employees.index') }}"><i class="side-menu__icon fe fe-user"></i><span class="side-menu__label">Employees</span></a>
                </li>

                <li class="sub-category">
                    <h3>Content</h3>
                </li>
                <li class="slide {{ request()->routeIs('admin.faq.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.faq.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-help-circle"></i>
                        <span class="side-menu__label">FAQ</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.faq.home') }}" class="slide-item {{ request()->routeIs('admin.faq.home') ? 'active' : '' }}">Home</a></li>
                        <li><a href="{{ route('admin.faq.searchrequest') }}" class="slide-item {{ request()->routeIs('admin.faq.searchrequest') ? 'active' : '' }}">Search request</a></li>
                    </ul>
                </li>
                <li class="slide {{ request()->routeIs('admin.blog.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-book-open"></i>
                        <span class="side-menu__label">Blog</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.blog.threads.index') }}" class="slide-item {{ request()->routeIs('admin.blog.threads.*') ? 'active' : '' }}">Posts</a></li>
                        <li><a href="{{ route('admin.blog.categories.index') }}" class="slide-item {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">Categories</a></li>
                    </ul>
                </li>
                <li class="slide {{ (request()->routeIs('admin.category.*') || request()->routeIs('admin.newblog.*')) ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ (request()->routeIs('admin.category.*') || request()->routeIs('admin.newblog.*')) ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-layers"></i>
                        <span class="side-menu__label">Category pages</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.category.target-fish.index') }}" class="slide-item {{ request()->routeIs('admin.category.target-fish.*') ? 'active' : '' }}">Target fish</a></li>
                        <li><a href="{{ route('admin.category.methods.index') }}" class="slide-item {{ request()->routeIs('admin.category.methods.*') ? 'active' : '' }}">Methods</a></li>
                        <li><a href="{{ route('admin.newblog.threads.index') }}" class="slide-item {{ request()->routeIs('admin.newblog.threads.*') ? 'active' : '' }}">Posts (guide blog)</a></li>
                        <li><a href="{{ route('admin.category.country.index') }}" class="slide-item {{ request()->routeIs('admin.category.country.*') ? 'active' : '' }}">Country</a></li>
                        <li><a href="{{ route('admin.category.vacation-country.index') }}" class="slide-item {{ request()->routeIs('admin.category.vacation-country.*') ? 'active' : '' }}">Vacation country</a></li>
                        <li><a href="{{ route('admin.category.trip-location.index') }}" class="slide-item {{ request()->routeIs('admin.category.trip-location.*') ? 'active' : '' }}">Trip locations</a></li>
                        <li><a href="{{ route('admin.category.region.index') }}" class="slide-item {{ request()->routeIs('admin.category.region.*') ? 'active' : '' }}">Region</a></li>
                        <li><a href="{{ route('admin.category.city.index') }}" class="slide-item {{ request()->routeIs('admin.category.city.*') ? 'active' : '' }}">City</a></li>
                    </ul>
                </li>
                <li class="slide {{ request()->routeIs('admin.page-attribute.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.page-attribute.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-layout"></i>
                        <span class="side-menu__label">Page attributes</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.page-attribute.en') }}" class="slide-item {{ request()->routeIs('admin.page-attribute.en') ? 'active' : '' }}">Catchaguide.com</a></li>
                        <li><a href="{{ route('admin.page-attribute.de') }}" class="slide-item {{ request()->routeIs('admin.page-attribute.de') ? 'active' : '' }}">Catchaguide.de</a></li>
                    </ul>
                </li>

                <li class="sub-category">
                    <h3>Guiding setup</h3>
                </li>
                <li class="slide {{ request()->routeIs('admin.settings.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-settings"></i>
                        <span class="side-menu__label">Guiding attributes</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.settings.levelindex') }}" class="slide-item {{ request()->routeIs('admin.settings.levelindex') ? 'active' : '' }}">Fishing level</a></li>
                        <li><a href="{{ route('admin.settings.fishingtypeindex') }}" class="slide-item {{ request()->routeIs('admin.settings.fishingtypeindex') ? 'active' : '' }}">Fishing type</a></li>
                        <li><a href="{{ route('admin.settings.equipmentindex') }}" class="slide-item {{ request()->routeIs('admin.settings.equipmentindex') ? 'active' : '' }}">Fishing equipment</a></li>
                        <li><a href="{{ route('admin.settings.fishingfromindex') }}" class="slide-item {{ request()->routeIs('admin.settings.fishingfromindex') ? 'active' : '' }}">Fishing from</a></li>
                        <li><a href="{{ route('admin.settings.inclussionindex') }}" class="slide-item {{ request()->routeIs('admin.settings.inclussionindex') ? 'active' : '' }}">Included</a></li>
                        <li><a href="{{ route('admin.settings.methodindex') }}" class="slide-item {{ request()->routeIs('admin.settings.methodindex') ? 'active' : '' }}">Method</a></li>
                        <li><a href="{{ route('admin.settings.waterindex') }}" class="slide-item {{ request()->routeIs('admin.settings.waterindex') ? 'active' : '' }}">Water types</a></li>
                        <li><a href="{{ route('admin.settings.targetindex') }}" class="slide-item {{ request()->routeIs('admin.settings.targetindex') ? 'active' : '' }}">Target fish</a></li>
                        <li><a href="{{ route('admin.settings.boat-extras.index') }}" class="slide-item {{ request()->routeIs('admin.settings.boat-extras.*') ? 'active' : '' }}">Boat extras</a></li>
                        <li><a href="{{ route('admin.settings.facilities.index') }}" class="slide-item {{ request()->routeIs('admin.settings.facilities.*') ? 'active' : '' }}">Facilities</a></li>
                        <li><a href="{{ route('admin.settings.kitchen-equipment.index') }}" class="slide-item {{ request()->routeIs('admin.settings.kitchen-equipment.*') ? 'active' : '' }}">Kitchen equipment</a></li>
                        <li><a href="{{ route('admin.settings.emailmaintenance') }}" class="slide-item {{ request()->routeIs('admin.settings.emailmaintenance') ? 'active' : '' }}">Email maintenance</a></li>
                    </ul>
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/></svg></div>
        </div>
    </div>
    <!--/APP-SIDEBAR-->
</div>
