@extends('layouts.app')
@section('title', 'Camp Offers - Vacations V2')

@section('meta_robots')
<meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div 
    x-data="campConfigurator({
        camp: @json($camp),
        accommodations: @json($accommodations),
        boats: @json($boats),
        guidings: @json($guidings),
        showCategories: {{ $showCategories ? 'true' : 'false' }}
    })"
    x-init="init()"
    class="camp-page min-h-screen bg-gradient-to-b from-slate-50 to-white"
>
    
    <!-- Camp Header -->
    <header class="camp-topbar">
        <div class="camp-container camp-topbar__inner">
            <div class="camp-topbar__info">
                <h1 class="camp-topbar__title">{{ $camp['title'] }}</h1>
                <div class="camp-topbar__meta">
                    <span>{{ $camp['city'] }}, {{ $camp['region'] }}, {{ $camp['country'] }}</span>
                    <span class="camp-topbar__dot">•</span>
                    <a class="camp-topbar__link" href="#map">Show on map</a>
                </div>
            </div>
            <div class="camp-topbar__actions">
                <a href="#configurator" class="brand-btn camp-topbar__cta">Make Inquiry</a>
                <span class="camp-topbar__note">Best Price Guarantee</span>
            </div>
        </div>
    </header>

    <!-- Gallery -->
    <div class="camp-container camp-gallery">
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
    <div class="camp-container camp-layout">
        <div class="camp-layout__content">
            <!-- Navigation -->
            <nav class="camp-nav flex flex-wrap text-sm">
                <a href="#general-info" class="nav-pill">General Information</a>
                <a href="#accommodations" class="nav-pill">Accommodations</a>
                <a href="#guidings" class="nav-pill">Guidings & Tours</a>
                <a href="#boats" class="nav-pill">Rental Boats</a>
            </nav>

            <!-- General Information -->
            <main id="general-info" class="camp-info-grid">
                <div class="camp-sections">
                    <section id="description" class="camp-section">
                        <h2 class="camp-section__title">Description</h2>
                        <div class="camp-section__body space-y-3">
                            @if(!empty($camp['description']['camp_description']))
                            <div>
                                <h3 class="font-semibold text-gray-700">Camp</h3>
                                <p>{{ $camp['description']['camp_description'] }}</p>
                            </div>
                            @endif
                            @if(!empty($camp['description']['camp_area']))
                            <div>
                                <h3 class="font-semibold text-gray-700">Area</h3>
                                <p>{{ $camp['description']['camp_area'] }}</p>
                            </div>
                            @endif
                            @if(!empty($camp['description']['camp_area_fishing']))
                            <div>
                                <h3 class="font-semibold text-gray-700">Fishing</h3>
                                <p>{{ $camp['description']['camp_area_fishing'] }}</p>
                            </div>
                            @endif
                        </div>
                    </section>

                    <section id="distances" class="camp-section">
                        <h2 class="camp-section__title">Distances</h2>
                        <div class="camp-pill-row">
                            @if(!empty($camp['distances']['to_shop_label']))
                                <span class="camp-pill">Shop: {{ $camp['distances']['to_shop_label'] }}</span>
                            @endif
                            @if(!empty($camp['distances']['to_town_label']))
                                <span class="camp-pill">Town: {{ $camp['distances']['to_town_label'] }}</span>
                            @endif
                            @if(!empty($camp['distances']['to_airport_label']))
                                <span class="camp-pill">Airport: {{ $camp['distances']['to_airport_label'] }}</span>
                            @endif
                            @if(!empty($camp['distances']['to_ferry_label']))
                                <span class="camp-pill">Ferry: {{ $camp['distances']['to_ferry_label'] }}</span>
                            @endif
                        </div>
                    </section>

                    @if(!empty($camp['amenities']))
                    <section id="amenities-section" class="camp-section">
                        <h2 class="camp-section__title">Camp Amenities</h2>
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
                        <h2 class="camp-section__title">Policies & Regulations</h2>
                        <ul class="camp-section__list">
                            @foreach($camp['policies_regulations'] as $policy)
                                <li>{{ $policy }}</li>
                            @endforeach
                        </ul>
                    </section>
                    @endif

                    @if(!empty($camp['best_travel_times']) || !empty($camp['best_travel_times_text']) || !empty($camp['target_fish']))
                    <section id="target-fish" class="camp-section">
                        <h2 class="camp-section__title">Best Travel Times & Target Fish</h2>
                        
                        @if(!empty($camp['best_travel_times']))
                            <div class="mb-4">
                                <h3 class="camp-section__subtitle">Best Travel Times</h3>
                                <ul class="camp-section__list">
                                    @foreach($camp['best_travel_times'] as $time)
                                        <li><strong>{{ $time['month'] }}</strong>: {{ $time['note'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif(!empty($camp['best_travel_times_text']))
                            <div class="mb-4">
                                <h3 class="camp-section__subtitle">Best Travel Times</h3>
                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $camp['best_travel_times_text'] }}</div>
                            </div>
                        @endif
                        
                        @if(!empty($camp['target_fish']))
                            <div>
                                <h3 class="camp-section__subtitle">Target Fish</h3>
                                <div class="camp-pill-row">
                                    @foreach($camp['target_fish'] as $fish)
                                        <span class="camp-pill">{{ $fish }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>
                    @endif

                    @if(!empty($camp['travel_info']))
                    <section id="travel-info" class="camp-section">
                        <h2 class="camp-section__title">Travel Information</h2>
                        <ul class="camp-section__list">
                            @foreach($camp['travel_info'] as $info)
                                @if(!empty($info))
                                    <li>{{ $info }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </section>
                    @endif

                    @if(!empty($camp['extras']))
                    <section id="extras" class="camp-section">
                        <h2 class="camp-section__title">Extras</h2>
                        <div class="camp-pill-row">
                            @foreach($camp['extras'] as $extra)
                                <span class="camp-pill">{{ $extra }}</span>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    @if(!empty($camp['conditions']['minimum_stay_nights']) || !empty($camp['conditions']['booking_window']))
                    <section id="conditions" class="camp-section">
                        <h2 class="camp-section__title">Camp Conditions</h2>
                        <div class="camp-section__cols">
                            @if(!empty($camp['conditions']['minimum_stay_nights']))
                                <div>Minimum stay: <strong>{{ $camp['conditions']['minimum_stay_nights'] }} nights</strong></div>
                            @endif
                            @if(!empty($camp['conditions']['booking_window']))
                                <div>Booking window: <strong>{{ $camp['conditions']['booking_window'] }}</strong></div>
                            @endif
                        </div>
                    </section>
                    @endif
                </div>
            </main>
        </div>

        <!-- Sidebar Configurator -->
        <aside id="configurator" class="camp-config">
            <div class="camp-config-card section-card">
                <h3 class="text-lg font-semibold" style="color: var(--brand); margin: 0;">Configure Trip</h3>
                <div class="accent-badge">
                    This offer is an <strong>inquiry</strong>. We confirm or decline within <strong>48 hours</strong>.
                </div>
                <div class="camp-form mt-4">
                    <div class="camp-form-grid">
                        <div class="camp-form-field full-span">
                            <label for="accSelect">Accommodation</label>
                            <select
                                id="accSelect"
                                class="camp-control"
                                x-model="selectedAccId"
                                @change="selectedAccId = $event.target.value"
                            >
                                @foreach ($accommodations as $acc)
                                    <option value="{{ $acc['id'] }}">
                                        {{ $acc['title'] }} - {{ number_format($acc['price']['amount'], 2, ',', '.') }} {{ $acc['price']['currency'] }} / night
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="camp-form-field">
                            <label for="boatSelect">Boat Rental</label>
                            <select
                                id="boatSelect"
                                class="camp-control"
                                x-model="selectedBoatId"
                                @change="selectedBoatId = $event.target.value || null"
                            >
                                <option value="">No boat</option>
                                @foreach ($boats as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['title'] }} - {{ number_format($b['price_per_day'], 2, ',', '.') }} {{ $b['currency'] }} / day
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="camp-form-field">
                            <label for="guideSelect">Guiding</label>
                            <select
                                id="guideSelect"
                                class="camp-control"
                                x-model="selectedGuideId"
                                @change="selectedGuideId = $event.target.value || null"
                            >
                                <option value="">No guiding</option>
                                @foreach ($guidings as $g)
                                    <option value="{{ $g['id'] }}">
                                        {{ $g['title'] }} - {{ number_format($g['price'], 2, ',', '.') }} {{ $g['currency'] }} fixed price
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="camp-form-field full-span">
                            <label for="guestInput">Persons</label>
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
                            <p class="camp-form-field__note" x-text="selectedAcc ? 'Maximum ' + selectedAcc.max_occupancy + ' persons for the selected accommodation.' : 'Maximum 10 persons.'"></p>
                        </div>
                        <div class="camp-form-field">
                            <label for="checkInInput">Check-in</label>
                            <input
                                id="checkInInput"
                                type="date"
                                x-model="checkIn"
                                class="camp-control"
                            >
                        </div>
                        <div class="camp-form-field">
                            <label for="checkOutInput">Check-out</label>
                            <input
                                id="checkOutInput"
                                type="date"
                                x-model="checkOut"
                                class="camp-control"
                            >
                        </div>
                    </div>
                    <div class="camp-form__status" x-text="nights ? nights + ' nights selected' : 'Please select travel dates'"></div>
                </div>
                <div class="camp-summary mt-4">
                    <div class="camp-summary__row">
                        <span>Accommodation</span>
                        <span x-text="selectedAcc ? fmt(accPrice, selectedAcc?.price?.currency ?? 'EUR') : '--'"></span>
                    </div>
                    <div class="camp-summary__row">
                        <span>Boat Rental</span>
                        <span x-text="selectedBoat ? fmt(boatPrice, selectedBoat?.currency ?? 'EUR') : '--'"></span>
                    </div>
                    <div class="camp-summary__row">
                        <span>Guiding</span>
                        <span x-text="selectedGuide ? fmt(guidePrice, selectedGuide?.currency ?? 'EUR') : '--'"></span>
                    </div>
                    <div class="camp-summary__row camp-summary__total">
                        <span>Total</span>
                        <span x-text="total ? fmt(total, 'EUR') : '--'"></span>
                    </div>
                    <p class="camp-summary__note">Send inquiry - binding confirmation within 48 hours.</p>
                </div>
                <button class="brand-btn">Send Inquiry</button>
            </div>
        </aside>
    </div>

    <!-- Full Width Card Sections -->
    <div class="camp-container">
        <!-- Accommodations Section -->
        <section id="accommodations" class="camp-section mb-3">
            <h2 class="camp-section__title">Accommodations</h2>
            @foreach($accommodations as $accommodation)
                <div class="mb-4">
                    <x-accommodation.card :accommodation="$accommodation" />
                </div>
            @endforeach
        </section>

        <!-- Guidings Section -->
        <section id="guidings" class="camp-section mb-3">
            <h2 class="camp-section__title">Guidings & Tours</h2>
            <x-guiding.card :guiding="$guiding" />
        </section>

        <!-- Rental Boats Section -->
        <section id="boats" class="camp-section mb-3">
            <h2 class="camp-section__title">Rental Boats</h2>
            <x-rental-boat.card :boat="$boat" />
        </section>
    </div>
</div>

<!-- Gallery Modal Script -->
<script>
    console.log('Gallery script loading...');
    // Gallery Modal Functions
    (function() {
        const galleryImages = @json($galleryImages);
        console.log('Gallery images loaded:', galleryImages.length);
        let currentGalleryIndex = 0;

            function openGalleryModal(index) {
                console.log('Opening gallery at index:', index);
                currentGalleryIndex = index;
                updateGalleryModal();
                document.getElementById('galleryModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeGalleryModal() {
                console.log('Closing gallery');
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
                console.log('Initializing gallery modal, images count:', galleryImages.length);
                
                // Add click handlers to all gallery items
                const galleryItems = document.querySelectorAll('[data-gallery-index]');
                console.log('Found gallery items:', galleryItems.length);
                
                galleryItems.forEach(function(item) {
                    item.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-gallery-index'));
                        console.log('Gallery item clicked, index:', index);
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

@endsection

@push('scripts')
    @once
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endonce
    
    <x-cards-scripts />
@endpush
