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
    $financeStrategyActive = request()->routeIs(
        'admin.finance.*',
        'admin.financial.*',
        'admin.strategy.*',
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
                <li class="slide {{ request()->routeIs('admin.guides.*') || request()->routeIs('admin.guide-requests.*') || request()->routeIs('admin.guide-analytics.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.guides.*') || request()->routeIs('admin.guide-requests.*') || request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-anchor"></i>
                        <span class="side-menu__label">Guides</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.guides.index') }}" class="slide-item {{ request()->routeIs('admin.guides.*') && !request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}">All guides</a></li>
                        <li><a href="{{ route('admin.guide-requests.index') }}" class="slide-item {{ request()->routeIs('admin.guide-requests.*') ? 'active' : '' }}">Guide requests</a></li>
                        <li><a href="{{ route('admin.guide-analytics.index') }}" class="slide-item {{ request()->routeIs('admin.guide-analytics.*') ? 'active' : '' }}">Guide analytics</a></li>
                    </ul>
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
                <li class="slide {{ $financeStrategyActive ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ $financeStrategyActive ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-layers"></i>
                        <span class="side-menu__label">Finance &amp; strategy</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.financial.dashboard') }}" class="slide-item {{ request()->routeIs('admin.financial.dashboard') ? 'active' : '' }}">Tracking dashboard</a></li>
                        <li><a href="{{ route('admin.finance.analytics') }}" class="slide-item {{ request()->routeIs('admin.finance.analytics') ? 'active' : '' }}">Analytics</a></li>
                        <li><a href="{{ route('admin.finance.invoices') }}" class="slide-item {{ request()->routeIs('admin.finance.invoices') ? 'active' : '' }}">Invoices</a></li>
                        <li><a href="{{ route('admin.strategy.supply-gaps') }}" class="slide-item {{ request()->routeIs('admin.strategy.*') ? 'active' : '' }}">Strategy</a></li>
                    </ul>
                </li>

                <li class="sub-category">
                    <h3>Catalog</h3>
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
                    <a class="side-menu__item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.reviews.index') }}"><i class="side-menu__icon fe fe-star"></i><span class="side-menu__label">{{ __('admin.reviews.nav') }}</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.contact-requests.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.contact-requests.index') }}"><i class="side-menu__icon fe fe-inbox"></i><span class="side-menu__label">Contact requests</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.product-reports.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.product-reports.index') }}"><i class="side-menu__icon fe fe-flag"></i><span class="side-menu__label">Product reports</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.newsletter-subscribers.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.newsletter-subscribers.index') }}"><i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">Newsletter subscribers</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.offer-sendout.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.offer-sendout.index') }}"><i class="side-menu__icon fe fe-send"></i><span class="side-menu__label">Custom camp offers</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.email-logs.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.email-logs.index') }}"><i class="side-menu__icon fe fe-mail"></i><span class="side-menu__label">Email logs</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.settings.emailmaintenance') || request()->routeIs('admin.settings.email.preview*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.settings.emailmaintenance') }}"><i class="side-menu__icon fe fe-layout"></i><span class="side-menu__label">Email templates</span></a>
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
                    <h3>{{ __('admin.security.section') }}</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.security.*') ? 'active' : '' }}" data-bs-toggle="slide" href="{{ route('admin.security.threats') }}"><i class="side-menu__icon fe fe-shield"></i><span class="side-menu__label">{{ __('admin.security.nav') }}</span></a>
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
                        <li><a href="{{ route('admin.faq.vacations') }}" class="slide-item {{ request()->routeIs('admin.faq.vacations') ? 'active' : '' }}">Vacations</a></li>
                        <li><a href="{{ route('admin.faq.vacation-trips') }}" class="slide-item {{ request()->routeIs('admin.faq.vacation-trips') ? 'active' : '' }}">Vacation trips</a></li>
                        <li><a href="{{ route('admin.faq.vacation-camps') }}" class="slide-item {{ request()->routeIs('admin.faq.vacation-camps') ? 'active' : '' }}">Vacation camps</a></li>
                        <li><a href="{{ route('admin.faq.offers') }}" class="slide-item {{ request()->routeIs('admin.faq.offers') ? 'active' : '' }}">Offers (Angebote)</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"
                       href="{{ route('admin.terms.index') }}">
                        <i class="side-menu__icon fe fe-file-text"></i>
                        <span class="side-menu__label">Terms & Conditions</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.monthly-highlights.*') ? 'active' : '' }}"
                       href="{{ route('admin.monthly-highlights.index') }}">
                        <i class="side-menu__icon fe fe-calendar"></i>
                        <span class="side-menu__label">Monthly Highlights</span>
                    </a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.vacation-testimonials.*') ? 'active' : '' }}"
                       href="{{ route('admin.vacation-testimonials.index') }}">
                        <i class="side-menu__icon fe fe-star"></i>
                        <span class="side-menu__label">{{ __('admin.vacation_testimonials.nav') }}</span>
                    </a>
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
                <li class="slide {{ (request()->routeIs('admin.category.*') && !request()->routeIs('admin.newblog.*')) ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ (request()->routeIs('admin.category.*') && !request()->routeIs('admin.newblog.*')) ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fe fe-layers"></i>
                        <span class="side-menu__label">{{ __('admin.category_pages.sidebar.menu') }}</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.category.hub') }}" class="slide-item {{ request()->routeIs('admin.category.hub') ? 'active' : '' }}">{{ __('admin.category_pages.sidebar.hub') }}</a></li>
                        <li><a href="{{ route('admin.category.destination-hub.edit') }}" class="slide-item {{ request()->routeIs('admin.category.destination-hub.*') ? 'active' : '' }}">{{ __('admin.category_pages.sidebar.destination_hub') }}</a></li>
                        <li><a href="{{ route('admin.category.target-fish.index') }}" class="slide-item {{ request()->routeIs('admin.category.target-fish.*') ? 'active' : '' }}">{{ __('admin.category_pages.sidebar.target_fish') }}</a></li>
                        <li><a href="{{ route('admin.category.methods.index') }}" class="slide-item {{ request()->routeIs('admin.category.methods.*') ? 'active' : '' }}">{{ __('admin.category_pages.sidebar.methods') }}</a></li>
                        <li><a href="{{ route('admin.category.country.index') }}" class="slide-item {{ request()->routeIs('admin.category.country.*') ? 'active' : '' }}">{{ __('admin.category_pages.sidebar.countries') }}</a></li>
                        <li><a href="{{ route('admin.category.region.index') }}" class="slide-item {{ request()->routeIs('admin.category.region.*') ? 'active' : '' }}">{{ __('admin.category_pages.sidebar.region') }}</a></li>
                        <li><a href="{{ route('admin.category.city.index') }}" class="slide-item {{ request()->routeIs('admin.category.city.*') ? 'active' : '' }}">{{ __('admin.category_pages.sidebar.city') }}</a></li>
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
                    <h3>{{ __('admin.listing_attributes.section') }}</h3>
                </li>
                @foreach(\App\Services\Admin\ListingAttributeRegistry::groups() as $group)
                    @php
                        $groupTypes = \App\Services\Admin\ListingAttributeRegistry::forGroup($group);
                        $groupTypeSlugs = array_keys($groupTypes);
                        $groupActive = request()->routeIs('admin.settings.attributes.*')
                            && in_array(request()->route('type'), $groupTypeSlugs, true);
                        // Also expand when hitting a legacy alias that maps into this group
                        if (! $groupActive && request()->routeIs('admin.settings.*') && ! request()->routeIs('admin.settings.scheduled-tasks.*') && ! request()->routeIs('admin.settings.email*') && ! request()->routeIs('admin.settings.attributes.*')) {
                            $legacyPath = trim(str_replace(url('/admin/settings'), '', request()->url()), '/');
                            $legacyKey = explode('/', $legacyPath)[0] ?? '';
                            $resolved = \App\Services\Admin\ListingAttributeRegistry::resolveLegacySlug($legacyKey);
                            $groupActive = $resolved && isset($groupTypes[$resolved]);
                        }
                    @endphp
                    <li class="slide {{ $groupActive ? 'is-expanded' : '' }}">
                        <a class="side-menu__item {{ $groupActive ? 'active' : '' }}" data-bs-toggle="slide" href="#">
                            <i class="side-menu__icon fe fe-settings"></i>
                            <span class="side-menu__label">{{ __('admin.listing_attributes.groups.'.$group) }}</span>
                            <i class="angle fe fe-chevron-right"></i>
                        </a>
                        <ul class="slide-menu">
                            @foreach($groupTypes as $slug => $typeConfig)
                                <li>
                                    <a href="{{ route('admin.settings.attributes.index', $slug) }}"
                                       class="slide-item {{ request()->routeIs('admin.settings.attributes.*') && request()->route('type') === $slug ? 'active' : '' }}">
                                        {{ __('admin.listing_attributes.types.'.$typeConfig['label_key']) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
                <li class="slide {{ request()->routeIs('admin.settings.scheduled-tasks.*') ? 'is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->routeIs('admin.settings.scheduled-tasks.*') ? 'active' : '' }}"
                       href="{{ route('admin.settings.scheduled-tasks.index') }}">
                        <i class="side-menu__icon fe fe-clock"></i>
                        <span class="side-menu__label">{{ __('admin.listing_attributes.scheduled_tasks') }}</span>
                    </a>
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/></svg></div>
        </div>
    </div>
    <!--/APP-SIDEBAR-->
</div>
