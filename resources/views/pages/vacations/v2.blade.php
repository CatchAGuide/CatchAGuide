@extends('layouts.app')
@section('title', $camp['title'] ?? 'Camp Offers - Vacations V2')

@section('meta_robots')
<meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div 
    x-data="campConfigurator({
        camp: @json($camp),
        accommodations: @json($accommodations),
        boats: @json($boats),
        guidings: @json($guidingsDropdown),
        showCategories: {{ $showCategories ? 'true' : 'false' }}
    })"
    x-init="init()"
    class="camp-page min-h-screen bg-gradient-to-b from-slate-50 to-white"
>
    
    <!-- Camp Header -->
    <header class="camp-topbar">
        <div class="camp-container camp-topbar__inner">
            <div class="camp-topbar__info">
                <h1 class="camp-topbar__title">{{ translate($camp['title']) }}</h1>
                <div class="camp-topbar__meta">
                    <span>{{ $camp['city'] }}, {{ $camp['region'] }}, {{ $camp['country'] }}</span>
                    <span class="camp-topbar__dot">•</span>
                    <a class="camp-topbar__link" href="#map">{{ __('vacations.show_on_map') }}</a>
                </div>
            </div>
            {{-- <div class="camp-topbar__actions"> --}}
                {{-- <a href="#configurator" class="brand-btn camp-topbar__cta">{{ __('vacations.make_inquiry') }}</a>
                <span class="camp-topbar__note">{{ __('vacations.best_price_guarantee') }}</span> --}}
            {{-- </div> --}}
        </div>
    </header>

    <!-- Gallery -->
    <div class="camp-container">
        <div class="camp-gallery">
            <div class="camp-gallery__main" data-gallery-index="0">
                <img src="{{ $primaryImage }}" alt="{{ $camp['title'] }}">
            </div>
            <div class="camp-gallery__right">
                @foreach ($topRightImages as $index => $image)
                    <div class="camp-gallery__thumb" data-gallery-index="{{ $index + 1 }}">
                        <img src="{{ $image }}" alt="{{ $camp['title'] }}">
                    </div>
                @endforeach
            </div>
            <div class="camp-gallery__bottom">
                @foreach ($bottomStripImages as $index => $image)
                    <div class="camp-gallery__thumb" data-gallery-index="{{ $index + 3 }}">
                        <img src="{{ $image }}" alt="{{ $camp['title'] }}">
                        @if($loop->last && $remainingGalleryCount > 0)
                            <div class="camp-gallery__more">+{{ $remainingGalleryCount }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Mobile Horizontal Scrollable Gallery (hidden on desktop) -->
        @php
            $mobileCarouselImages = array_slice($galleryImages, 1); // All images except the first (primary)
        @endphp
        @if(count($mobileCarouselImages) > 0)
        <div class="camp-gallery__mobile-carousel">
            <div class="camp-gallery__mobile-carousel-scroll">
                @foreach($mobileCarouselImages as $index => $image)
                    <div class="camp-gallery__mobile-carousel-item" data-gallery-index="{{ $index + 1 }}">
                        <img src="{{ $image }}" alt="{{ $camp['title'] }} - Image {{ $index + 2 }}">
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Gallery Modal -->
    <div id="galleryModal" class="gallery-modal">
        <div class="gallery-modal__content">
            <button class="gallery-modal__close">&times;</button>
            <button class="gallery-modal__prev">&#10094;</button>
            <button class="gallery-modal__next">&#10095;</button>
            <img id="galleryModalImage" src="" alt="{{ $camp['title'] }}">
            <div class="gallery-modal__counter">
                <span id="galleryCurrentIndex">1</span> / <span id="galleryTotalCount">{{ count($galleryImages) }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content with Sidebar -->
    <div class="camp-container camp-layout" style="grid-template-columns: 1fr;">
        <div class="camp-layout__content">
            <!-- Navigation -->
            <nav class="camp-nav-enhanced">
                @php
                    $accommodationsCount = count($accommodations ?? []);
                    $boatsCount = count($boats ?? []);
                    $specialOffersCount = isset($specialOffers) ? count($specialOffers) : 0;
                    $guidingsCount = isset($guidings) ? count($guidings) : 0;
                @endphp
                
                @if($accommodationsCount > 0)
                <a href="#accommodations" class="camp-nav-item">
                    <div class="camp-nav-item__name">{{ __('vacations.accommodations') }}</div>
                </a>
                @endif
                
                @if($boatsCount > 0)
                <a href="#boats" class="camp-nav-item">
                    <div class="camp-nav-item__name">{{ __('vacations.rental_boats') }}</div>
                </a>
                @endif
                
                @if($specialOffersCount > 0)
                <a href="#special-offers" class="camp-nav-item">
                    <div class="camp-nav-item__name">{{ __('vacations.special_offers') }}</div>
                </a>
                @endif
                
                @if($guidingsCount > 0)
                <a href="#guidings" class="camp-nav-item">
                    <div class="camp-nav-item__name">{{ __('vacations.guidings_tours') }}</div>
                </a>
                @endif
            </nav>

            <!-- Contact Card -->
            <div class="contact-card card p-3 mb-4">
                <div class="contact-card__content">
                    <div class="contact-card__header">
                        <h5 class="contact-card__title mb-1">@lang('vacations.contact_us')</h5>
                        <p class="contact-card__message mb-0 text-muted small">@lang('vacations.contact_us_message')</p>
                    </div>
                    <div class="contact-info">
                        <i class="fas fa-phone-alt me-2"></i>
                        <a href="tel:+49{{env('CONTACT_NUM')}}" class="text-decoration-none">+49 (0) {{env('CONTACT_NUM')}}</a>
                    </div>
                    <a href="#" id="contact-product" class="btn btn-outline-orange" data-bs-toggle="modal" data-bs-target="#contactModal">
                        @lang('vacations.contact_us_button')
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- General Information -->
            <main id="general-info" class="camp-info-grid">
                <div class="camp-sections">
                    <section id="description" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.description') }}</h2>
                        <div class="camp-section__body space-y-3">
                            @if(!empty($camp['description']['camp_description']))
                            <div>
                                <h3 class="font-semibold text-gray-700">{{ __('vacations.camp') }}</h3>
                                <p>{{ translate($camp['description']['camp_description']) }}</p>
                            </div>
                            @endif
                            @if(!empty($camp['description']['camp_area']))
                            <div>
                                <h3 class="font-semibold text-gray-700">{{ __('vacations.area') }}</h3>
                                <p>{{ translate($camp['description']['camp_area']) }}</p>
                            </div>
                            @endif
                            @if(!empty($camp['description']['camp_area_fishing']))
                            <div>
                                <h3 class="font-semibold text-gray-700">{{ __('vacations.fishing') }}</h3>
                                <p>{{ translate($camp['description']['camp_area_fishing']) }}</p>
                            </div>
                            @endif
                        </div>
                    </section>

                    <section id="distances" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.distances') }}</h2>
                        <div class="camp-pill-row">
                            @if(!empty($camp['distances']['to_shop_label']))
                                <span class="camp-pill">{{ __('vacations.shop') }}: {{ translate($camp['distances']['to_shop_label']) }}</span>
                            @endif
                            @if(!empty($camp['distances']['to_town_label']))
                                <span class="camp-pill">{{ __('vacations.town') }}: {{ translate($camp['distances']['to_town_label']) }}</span>
                            @endif
                            @if(!empty($camp['distances']['to_airport_label']))
                                <span class="camp-pill">{{ __('vacations.airport') }}: {{ translate($camp['distances']['to_airport_label']) }}</span>
                            @endif
                            @if(!empty($camp['distances']['to_ferry_label']))
                                <span class="camp-pill">{{ __('vacations.ferry') }}: {{ translate($camp['distances']['to_ferry_label']) }}</span>
                            @endif
                        </div>
                    </section>

                    @if(!empty($camp['amenities']))
                    <section id="amenities-section" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.camp_amenities') }}</h2>
                        <div class="camp-section__cols">
                            {{-- Dynamic amenities from camp_facility_camp pivot table --}}
                            @foreach($camp['amenities'] as $amenity)
                                <div class="flex items-center gap-2">
                                    <span>{{ $amenity['name'] }}</span>
                                    <i class="fa fa-check text-green-600"></i>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    @if(!empty($camp['policies_regulations']))
                    <section id="policies" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.policies_regulations') }}</h2>
                        <ul class="camp-section__list">
                            @foreach($camp['policies_regulations'] as $policy)
                                <li>{!! translate($policy) !!}</li>
                            @endforeach
                        </ul>
                    </section>
                    @endif

                    @if(!empty($camp['best_travel_times']) || !empty($camp['best_travel_times_parsed']))
                    <section id="best-travel-times" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.best_travel_times') }}</h2>
                        
                        @if(!empty($camp['best_travel_times']))
                            <ul class="camp-section__list">
                                @foreach($camp['best_travel_times'] as $time)
                                    <li><strong>{!! $time['month'] !!}</strong>: {!! $time['note'] !!}</li>
                                @endforeach
                            </ul>
                        @elseif(!empty($camp['best_travel_times_parsed']))
                            @foreach($camp['best_travel_times_parsed'] as $item)
                                @if(!empty($item['description']))
                                    <ul class="camp-section__list">
                                        <li><b>{{ translate($item['title']) }}:</b> {{ translate($item['description']) }}</li>
                                    </ul>
                                @endif
                            @endforeach
                        @endif
                    </section>
                    @endif
                    
                    @if(!empty($camp['target_fish']))
                    <section id="target-fish" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.target_fish') }}</h2>
                        <div class="camp-pill-row">
                            @foreach($camp['target_fish'] as $fish)
                                <span class="camp-pill">{{ $fish }}</span>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    @if(!empty($camp['travel_info']))
                    <section id="travel-info" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.travel_information') }}</h2>
                        <ul class="camp-section__list">
                            @foreach($camp['travel_info'] as $info)
                                @if(!empty($info))
                                    <li>{{ translate($info) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </section>
                    @endif

                    @if(!empty($camp['extras']))
                    <section id="extras" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.extras') }}</h2>
                        <div class="camp-pill-row">
                            @foreach($camp['extras'] as $extra)
                                <span class="camp-pill">{{ translate($extra) }}</span>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    @if(!empty($camp['conditions']['minimum_stay_nights']) || !empty($camp['conditions']['booking_window']))
                    <section id="conditions" class="camp-section">
                        <h2 class="camp-section__title">{{ __('vacations.camp_conditions') }}</h2>
                        <div class="camp-section__cols">
                            @if(!empty($camp['conditions']['minimum_stay_nights']))
                                <div>{{ __('vacations.minimum_stay') }}: <strong>{{ $camp['conditions']['minimum_stay_nights'] }} {{ __('vacations.nights') }}</strong></div>
                            @endif
                            @if(!empty($camp['conditions']['booking_window']))
                                <div>{{ __('vacations.booking_window') }}: <strong>{{ translate($camp['conditions']['booking_window']) }}</strong></div>
                            @endif
                        </div>
                    </section>
                    @endif
                </div>
            </main>
        </div>

        {{-- Sidebar Configurator - Commented out to hide Configure Trip section --}}
        {{-- <aside id="configurator" class="camp-config">
            <div class="camp-config-card section-card">
                <h3 class="text-lg font-semibold" style="color: var(--brand); margin: 0;">{{ __('vacations.configure_trip') }}</h3>
                <div class="accent-badge">
                    {!! __('vacations.inquiry_notice') !!}
                </div>
                <div class="camp-form mt-4">
                    <div class="camp-form-grid">
                        <div class="camp-form-field full-span">
                            <label for="accSelect">{{ __('vacations.accommodation') }}</label>
                            <select
                                id="accSelect"
                                class="camp-control"
                                x-model="selectedAccId"
                                @change="selectedAccId = $event.target.value"
                            >
                                @foreach ($accommodations as $acc)
                                    <option value="{{ $acc['id'] }}">
                                        {{ $acc['title'] }} - {{ number_format($acc['price']['amount'], 2, ',', '.') }} {{ $acc['price']['currency'] }} {{ __('vacations.per_night') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="camp-form-field">
                            <label for="boatSelect">{{ __('vacations.boat_rental') }}</label>
                            <select
                                id="boatSelect"
                                class="camp-control"
                                x-model="selectedBoatId"
                                @change="selectedBoatId = $event.target.value || null"
                            >
                                <option value="">{{ __('vacations.no_boat') }}</option>
                                @foreach ($boats as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['title'] }} - {{ number_format($b['price_per_day'], 2, ',', '.') }} {{ $b['currency'] }} {{ __('vacations.per_day') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="camp-form-field">
                            <label for="guideSelect">{{ __('vacations.guidings') }}</label>
                            <select
                                id="guideSelect"
                                class="camp-control"
                                x-model="selectedGuideId"
                                @change="selectedGuideId = $event.target.value || null"
                            >
                                <option value="">{{ __('vacations.no_guiding') }}</option>
                                @foreach ($guidingsDropdown as $g)
                                    <option value="{{ $g['id'] }}">
                                        {{ $g['title'] }} - {{ number_format($g['price'], 2, ',', '.') }} {{ $g['currency'] }} {{ __('vacations.fixed_price') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="camp-form-field full-span">
                            <label for="guestInput">{{ __('vacations.persons') }}</label>
                            <input
                                id="guestInput"
                                type="number"
                                min="1"
                                :max="selectedAcc ? selectedAcc.max_occupancy : 10"
                                x-model.number="guests"
                                @input="
                                    const max = selectedAcc ? selectedAcc.max_occupancy : 10;
                                    if (!guests || guests < 1) { guests = 1; }
                                    if (guests > max) { guests = max; }
                                "
                                class="camp-control"
                            >
                            <p class="camp-form-field__note" x-text="selectedAcc ? '{{ __('vacations.maximum') }} ' + selectedAcc.max_occupancy + ' {{ __('vacations.persons_for_accommodation') }}' : '{{ __('vacations.maximum_persons') }}'"></p>
                        </div>
                        <div class="camp-form-field">
                            <label for="checkInInput">{{ __('vacations.check_in') }}</label>
                            <input
                                id="checkInInput"
                                type="date"
                                x-model="checkIn"
                                class="camp-control"
                            >
                        </div>
                        <div class="camp-form-field">
                            <label for="checkOutInput">{{ __('vacations.check_out') }}</label>
                            <input
                                id="checkOutInput"
                                type="date"
                                x-model="checkOut"
                                class="camp-control"
                            >
                        </div>
                    </div>
                    <div class="camp-form__status" x-text="nights ? nights + ' {{ __('vacations.nights_selected') }}' : '{{ __('vacations.select_travel_dates') }}'"></div>
                </div>
                <div class="camp-summary mt-4">
                    <div class="camp-summary__row">
                        <span>{{ __('vacations.accommodation') }}</span>
                        <span x-text="selectedAcc ? fmt(accPrice, selectedAcc?.price?.currency ?? 'EUR') : '--'"></span>
                    </div>
                    <div class="camp-summary__row">
                        <span>{{ __('vacations.boat_rental') }}</span>
                        <span x-text="selectedBoat ? fmt(boatPrice, selectedBoat?.currency ?? 'EUR') : '--'"></span>
                    </div>
                    <div class="camp-summary__row">
                        <span>{{ __('vacations.guidings') }}</span>
                        <span x-text="selectedGuide ? fmt(guidePrice, selectedGuide?.currency ?? 'EUR') : '--'"></span>
                    </div>
                    <div class="camp-summary__row camp-summary__total">
                        <span>{{ __('vacations.total') }}</span>
                        <span x-text="total ? fmt(total, 'EUR') : '--'"></span>
                    </div>
                    <p class="camp-summary__note">{{ __('vacations.send_inquiry_notice') }}</p>
                </div>
                <button class="brand-btn">{{ __('vacations.send_inquiry') }}</button>
            </div>
        </aside> --}}
    </div>

    <!-- Full Width Card Sections -->
    <div class="camp-container">
        <!-- Map Section -->
        <div id="map" class="mb-5" style="height: 400px;">
            <!-- Google Map will be rendered here -->
        </div>

        <!-- Special Offers Section -->
        @if (isset($specialOffers) && count($specialOffers) > 0)
        <section id="special-offers" class="camp-section mb-3">
            <h2 class="camp-section__title">{{ __('Special Offers') }}</h2>
            @foreach($specialOffers as $specialOffer)
                <div class="mb-4">
                    <x-special-offer.card :specialOffer="$specialOffer" />
                </div>
            @endforeach
        </section>
        @endif

        <!-- Accommodations Section -->
        @if (count($accommodations) > 0)
        <section id="accommodations" class="camp-section mb-3">
            <h2 class="camp-section__title">{{ __('vacations.accommodations') }}</h2>
            @foreach($accommodations as $accommodation)
                <div class="mb-4">
                    <x-accommodation.card :accommodation="$accommodation" />
                </div>
            @endforeach
        </section>
        @endif

        <!-- Guidings Section -->
        @if (isset($guidings) && count($guidings) > 0)
        <section id="guidings" class="camp-section mb-3">
            <h2 class="camp-section__title">{{ __('vacations.guidings_tours') }}</h2>
            @foreach($guidings as $guiding)
                <div class="mb-4">
                    <x-guiding.card :guiding="$guiding" />
                </div>
            @endforeach
        </section>
        @endif

        <!-- Rental Boats Section -->
        @if (count($boats) > 0)
        <section id="boats" class="camp-section mb-3">
            <h2 class="camp-section__title">{{ __('vacations.rental_boats') }}</h2>
            @foreach($boats as $boat)
                <div class="mb-4">
                    <x-rental-boat.card :boat="$boat" />
                </div>
            @endforeach
        </section>
        @endif
    </div>
</div>

<!-- Gallery Modal Script -->
<script>
    // Gallery Modal Functions
    (function() {
        const galleryImages = @json($galleryImages);
        let currentGalleryIndex = 0;

            function openGalleryModal(index) {
                currentGalleryIndex = index;
                updateGalleryModal();
                document.getElementById('galleryModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeGalleryModal() {
                document.getElementById('galleryModal').style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            function changeGalleryImage(direction) {
                currentGalleryIndex += direction;
                if (currentGalleryIndex < 0) currentGalleryIndex = galleryImages.length - 1;
                if (currentGalleryIndex >= galleryImages.length) currentGalleryIndex = 0;
                updateGalleryModal();
            }

            function updateGalleryModal() {
                document.getElementById('galleryModalImage').src = galleryImages[currentGalleryIndex];
                document.getElementById('galleryCurrentIndex').textContent = currentGalleryIndex + 1;
            }

            function initGallery() {
                // Add click handlers to all gallery items
                const galleryItems = document.querySelectorAll('[data-gallery-index]');
                
                galleryItems.forEach(function(item) {
                    item.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-gallery-index'));
                        openGalleryModal(index);
                    });
                });

                // Modal close button
                const closeBtn = document.querySelector('.gallery-modal__close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        closeGalleryModal();
                    });
                }

                // Click outside to close
                const modal = document.getElementById('galleryModal');
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target.id === 'galleryModal') {
                            closeGalleryModal();
                        }
                    });
                }

                // Navigation buttons
                const prevBtn = document.querySelector('.gallery-modal__prev');
                const nextBtn = document.querySelector('.gallery-modal__next');
                
                if (prevBtn) {
                    prevBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        changeGalleryImage(-1);
                    });
                }
                
                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        changeGalleryImage(1);
                    });
                }

                // Keyboard navigation
                document.addEventListener('keydown', function(event) {
                    if (modal && modal.style.display === 'flex') {
                        if (event.key === 'Escape') {
                            closeGalleryModal();
                        } else if (event.key === 'ArrowLeft') {
                            changeGalleryImage(-1);
                        } else if (event.key === 'ArrowRight') {
                            changeGalleryImage(1);
                        }
                    }
                });
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initGallery);
            } else {
                // DOM is already ready
                initGallery();
        }
    })();
</script>

<script>
    document.addEventListener('alpine:init', () => {
            // Camp Configurator Component
            Alpine.data('campConfigurator', ({ camp, accommodations, boats, guidings, showCategories = true }) => ({
                camp,
                accommodations,
                boats,
                guidings,
                showCategories,
                checkIn: '',
                checkOut: '',
                guests: Math.min(2, accommodations[0]?.max_occupancy ?? 2),
                selectedAccId: accommodations[0]?.id ? String(accommodations[0].id) : null,
                selectedBoatId: null,
                selectedGuideId: null,
                
                init() {
                    this.$watch('selectedAccId', () => {
                        const max = this.selectedAcc ? this.selectedAcc.max_occupancy : 10;
                        if (this.guests > max) {
                            this.guests = max;
                        }
                        if (!this.guests || this.guests < 1) {
                            this.guests = 1;
                        }
                    });
                },
                
                fmt(value, currency = 'EUR') {
                    try {
                        return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
                    } catch (error) {
                        const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                        return `${amount} ${currency}`;
                    }
                },
                
                nightsBetween(start, end) {
                    if (!start || !end) return 0;
                    const startDate = new Date(start);
                    const endDate = new Date(end);
                    if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) return 0;
                    const diff = Math.floor((endDate.getTime() - startDate.getTime()) / 86400000);
                    return diff > 0 ? diff : 0;
                },
                
                blendedPrice(nights, perNight, perWeek) {
                    if (!nights || !perNight) return 0;
                    if (perWeek && nights >= 7) {
                        const weeks = Math.floor(nights / 7);
                        const rest = nights % 7;
                        return weeks * perWeek + rest * perNight;
                    }
                    return nights * perNight;
                },
                
                get selectedAcc() {
                    return this.accommodations.find(item => String(item.id) === String(this.selectedAccId)) ?? null;
                },
                
                get selectedBoat() {
                    return this.boats.find(item => String(item.id) === String(this.selectedBoatId)) ?? null;
                },
                
                get selectedGuide() {
                    return this.guidings.find(item => String(item.id) === String(this.selectedGuideId)) ?? null;
                },
                
                get nights() {
                    return this.nightsBetween(this.checkIn, this.checkOut);
                },
                
                get accPrice() {
                    if (!this.selectedAcc) return 0;
                    return this.blendedPrice(this.nights, this.selectedAcc.price?.amount, this.selectedAcc.price?.per_week);
                },
                
                get boatPrice() {
                    if (!this.selectedBoat) return 0;
                    return (this.selectedBoat.price_per_day || 0) * (this.nights || 0);
                },
                
                get guidePrice() {
                    if (!this.selectedGuide) return 0;
                    return this.selectedGuide.price || 0;
                },
                
                get total() {
                    return this.accPrice + this.boatPrice + this.guidePrice;
            },
        }));
    });
</script>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">{{ __('contact.shareYourQuestion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! ReCaptcha::htmlScriptTagJsApi() !!}
                <div id="contactFormContainer">
                    <form id="contactModalForm">
                        @csrf
                        <input type="hidden" name="source_type" value="camp">
                        <input type="hidden" name="source_id" value="{{ $camp['id'] }}">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="@lang('contact.yourName')" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="email" class="form-control" placeholder="@lang('contact.email')" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            @include('includes.forms.phone-input', [
                                'placeholder' => 'contact.phone'
                            ])
                        </div>
                        <div class="form-group mb-3">
                            <textarea name="description" class="form-control" rows="4" placeholder="@lang('contact.feedback')" required></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            {!! htmlFormSnippet() !!}
                            <button type="button" id="contactSubmitBtn" class="btn btn-orange">@lang('contact.btnSend')</button>
                        </div>
                    </form>
                </div>
                <!-- Loading Overlay -->
                <div id="contactLoadingOverlay" style="display: none;">
                    <div class="d-flex justify-content-center align-items-center flex-column p-4">
                        <div class="spinner-border text-orange mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-center">@lang('contact.submitting')...</p>
                    </div>
                </div>
                <div class="alert alert-success mt-3" id="contactSuccessMessage" style="display: none;">
                    @lang('contact.successMessage')
                </div>
                <div class="alert alert-danger mt-3" id="contactError" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Contact form submission handler
    $('#contactSubmitBtn').on('click', function() {
        handleContactFormSubmission();
    });
    
    // Also bind on modal shown event to ensure the button exists
    $('#contactModal').on('shown.bs.modal', function() {
        $('#contactSubmitBtn').off('click').on('click', function() {
            handleContactFormSubmission();
        });
    });
    
    function handleContactFormSubmission() {
        const contactForm = document.getElementById('contactModalForm');
        const contactFormContainer = document.getElementById('contactFormContainer');
        const loadingOverlay = document.getElementById('contactLoadingOverlay');
        const successMessage = document.getElementById('contactSuccessMessage');
        const contactError = document.getElementById('contactError');
        
        // Hide previous messages
        contactError.style.display = 'none';
        successMessage.style.display = 'none';
        
        // Validate form
        if (!contactForm.checkValidity()) {
            contactForm.reportValidity();
            return;
        }
        
        // Get form data
        const formData = new FormData(contactForm);
        
        // Show loading overlay
        contactFormContainer.style.display = 'none';
        loadingOverlay.style.display = 'block';
        
        // Submit form via AJAX
        fetch('{{route('sendcontactmail')}}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading overlay
            loadingOverlay.style.display = 'none';
            
            if (data.success) {
                // Reset form
                contactForm.reset();
                
                // Show success message
                successMessage.style.display = 'block';
                
                // Hide contact modal after 2 seconds
                setTimeout(() => {
                    const contactModal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
                    if (contactModal) {
                        contactModal.hide();
                    }
                    successMessage.style.display = 'none';
                    contactFormContainer.style.display = 'block';
                }, 2000);
            } else {
                contactError.style.display = 'block';
                contactError.innerHTML = data.message || 'An error occurred. Please try again.';
                contactFormContainer.style.display = 'block';
            }
        })
        .catch(error => {
            // Hide loading overlay and show form again on error
            loadingOverlay.style.display = 'none';
            contactFormContainer.style.display = 'block';
            
            contactError.style.display = 'block';
            contactError.innerHTML = error.message || 'An error occurred. Please try again.';
        });
    }
});
</script>

@endsection

@section('css_after')
<style>
  /* Mobile optimizations for contact modal form */
  @media (max-width: 576px) {
    /* Stack phone country code + number vertically inside modal */
    #contactModal .phone-input-container .d-flex {
      flex-direction: column;
    }

    #contactModal .phone-input-container .d-flex > * {
      width: 100% !important;
      max-width: 100% !important;
      margin-bottom: 0.5rem;
      border-radius: 0.25rem !important;
    }

    /* Stack ReCaptcha + submit button vertically and make button full width */
    #contactModal .modal-body .d-flex.justify-content-between {
      flex-direction: column;
      align-items: stretch;
      gap: 1rem;
    }

    #contactModal .modal-body .d-flex.justify-content-between > * {
      width: 100% !important;
    }

    #contactModal .btn.btn-orange {
      width: 100%;
    }
  }
</style>
@endsection

@section('js_after')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initMap();
});

function initMap() {
    @php
        // Try to get coordinates from camp data (support multiple possible field names)
        $lat = $camp['latitude'] ?? $camp['lat'] ?? 41.40338;
        $lng = $camp['longitude'] ?? $camp['lng'] ?? 2.17403;
    @endphp
    var location = { lat: {{ $lat }}, lng: {{ $lng }} };
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 10,
        center: location,
        mapTypeControl: false,
        streetViewControl: false,
        mapId: '8f348c2f6c51f6f0'
    });

    // Create an AdvancedMarkerElement with the required Map ID
    const marker = new google.maps.marker.AdvancedMarkerElement({
        map,
        position: location,
    });
}
</script>
@endsection

@push('scripts')
    @once
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endonce
    
    <x-cards-scripts />
@endpush
