<div x-data="accommodationCard(@js($accommodation))" class="accommodation-card">
    <div class="accommodation-card__grid">
        <!-- Left Column: Gallery + Details + Inclusives/Extras -->
        <div class="accommodation-card__left-column">
            <!-- Gallery -->
            <div class="accommodation-gallery">
                <img :src="currentImage || 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop'" :alt="accommodation.title || 'Apartment'" />
                
                <div>
                    <button 
                        type="button"
                        aria-label="Previous image" 
                        @click="prevImage()"
                        class="accommodation-gallery__nav-btn accommodation-gallery__nav-btn--prev"
                    >
                        ‹
                    </button>
                    <button 
                        type="button"
                        aria-label="Next image" 
                        @click="nextImage()"
                        class="accommodation-gallery__nav-btn accommodation-gallery__nav-btn--next"
                    >
                        ›
                    </button>
                    <div class="accommodation-gallery__counter" x-text="(currentImageIndex + 1) + '/' + (images.length || 3)">1/3</div>
                </div>

                <div class="accommodation-gallery__info-chips">
                    <span class="accommodation-gallery__info-chip">
                        👥 <span x-text="accommodation.max_occupancy || '4'">4</span> Pers
                    </span>
                    <span class="accommodation-gallery__info-chip">
                        🛏️ <span x-text="accommodation.number_of_bedrooms || '2'">2</span> BR
                    </span>
                    <span class="accommodation-gallery__info-chip">
                        🛁 <span x-text="accommodation.bathrooms || '1'">1</span> Bath
                    </span>
                    <span class="accommodation-gallery__info-chip">
                        📐 <span x-text="accommodation.living_area_sqm || '80'">80</span> m²
                    </span>
                </div>
            </div>

            <!-- Details Section -->
            <div class="accommodation-card__detail-box">
                <div class="accommodation-card__detail-box-title">Details</div>
                <ul class="accommodation-card__detail-list">
                    <li>Floor(s): <span class="font-medium" x-text="accommodation.floors || 'EG'">EG</span></li>
                    <li>Built/Renovated: <span class="font-medium" x-text="accommodation.year_or_renovated || 'Renovated 2023'">Renovated 2023</span></li>
                    <li>Living room: <span class="font-medium" x-text="accommodation.living_room ? 'Yes' : 'No'">Yes</span></li>
                    <li>Dining room: <span class="font-medium" x-text="accommodation.dining_room ? 'Yes' : 'No'">Yes</span></li>
                </ul>
            </div>

            <!-- Inclusives & Extras -->
            <div class="accommodation-card__detail-box">
                <div class="accommodation-card__detail-box-title">Included & Extras</div>
                
                <div>
                    <div class="accommodation-card__detail-box-title" style="font-size: 11px; margin-bottom: 2px;">Included</div>
                    <div class="accommodation-card__inclusive-extras">
                        <span class="accommodation-card__inclusive-chip">✅ WiFi</span>
                        <span class="accommodation-card__inclusive-chip">✅ Electricity/Heating</span>
                    </div>
                </div>

                <div style="margin-top: 8px;">
                    <div class="accommodation-card__detail-box-title" style="font-size: 11px; margin-bottom: 2px;">Extras</div>
                    <div class="accommodation-card__inclusive-extras">
                        <span class="accommodation-card__inclusive-chip">✅ Bed linen</span>
                        <span class="accommodation-card__inclusive-chip">✅ Towels</span>
                        <span class="accommodation-card__inclusive-chip">✅ Final cleaning</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Header, Price, Chips, Matrix, Amenities -->
        <div class="accommodation-card__right-column">
            <!-- Header & Pricing -->
            <div class="accommodation-card__header">
                <div class="accommodation-card__info">
                    <div class="accommodation-card__location">
                        📍 <span x-text="accommodation.city + ', ' + accommodation.region + ', ' + accommodation.country">Location</span>
                    </div>
                    <h3 class="accommodation-card__title" x-text="accommodation.title">Apartment Title</h3>
                    <div class="accommodation-card__type" x-text="accommodation.accommodation_type">Apartment / Holiday Home</div>
                    <p class="accommodation-card__description" x-text="accommodation.description">Description</p>
                </div>
                <div class="accommodation-card__pricing">
                    <div class="accommodation-card__price-type" x-text="accommodation.price?.type || 'per night'">per night</div>
                    <div class="accommodation-card__price-amount" x-text="accommodation.price?.amount ? fmt(accommodation.price.amount, accommodation.price.currency || 'EUR') : '€110.00'">€110.00</div>
                    <button class="accommodation-card__select-btn">
                        Select Accommodation
                    </button>
                </div>
            </div>

            <!-- Distance Chips -->
            <div class="accommodation-card__distance-chips">
                <span class="accommodation-card__distance-chip">
                    🌊 Water: <span x-text="accommodation.distances?.to_water_m || '40'">40</span> m
                </span>
                <span class="accommodation-card__distance-chip">
                    ⚓ Jetty: <span x-text="accommodation.distances?.to_berth_m || '60'">60</span> m
                </span>
                <span class="accommodation-card__distance-chip">
                    🚗 Parking: <span x-text="accommodation.distances?.to_parking_m || '20'">20</span> m
                </span>
            </div>

            <!-- Info Matrix: 3 boxes top, 4th bottom full-width -->
            <div class="accommodation-card__info-matrix">
                <!-- Box 1: Beds & Location -->
                <div class="accommodation-card__info-box">
                    <div class="accommodation-card__info-box-title">Beds & Location</div>
                    <div class="accommodation-card__info-box-content">
                        <div class="font-medium" x-text="getBedSummary() || '2× Single • 1× Double'">2× Single • 1× Double</div>
                        <div style="margin-top: 8px;">
                            <div class="accommodation-card__info-box-title">Location</div>
                            <p style="color: #334155; line-height: 1.4;" x-text="accommodation.location_description || 'Near the shore'">Near the shore</p>
                        </div>
                    </div>
                </div>

                <!-- Box 2: Bath/Laundry -->
                <div class="accommodation-card__info-box">
                    <div class="accommodation-card__info-box-title">Bath/Laundry</div>
                    <div class="accommodation-card__info-box-content" x-text="getBathList().join(' · ') || 'Bath details'">Bath details</div>
                </div>

                <!-- Box 3: Kitchen -->
                <div class="accommodation-card__info-box">
                    <div class="accommodation-card__info-box-title">Kitchen</div>
                    <div class="accommodation-card__info-box-content" x-text="getKitchenList().join(' · ') || 'Kitchen details'">Kitchen details</div>
                </div>

                <!-- Box 4: Policies & Conditions (full width) -->
                <div class="accommodation-card__info-box">
                    <div class="accommodation-card__info-box-title">Policies & Conditions</div>
                    <div class="accommodation-card__info-box-content" x-text="getPolicyList().slice(0, 6).join(' · ') || 'Policies'">Policies</div>
                    <div class="accommodation-card__info-box-title" style="margin-top: 8px;">Conditions</div>
                    <div class="accommodation-card__info-box-content" x-text="getConditions() || 'Conditions'">Conditions</div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="accommodation-card__amenities-section">
                <div class="accommodation-card__amenities-title">Amenities</div>
                <div class="accommodation-card__amenities-chips">
                    <span class="accommodation-card__amenity-chip">WiFi</span>
                    <span class="accommodation-card__amenity-chip">Fishing room</span>
                    <span class="accommodation-card__amenity-chip">Filleting station</span>
                    <span class="accommodation-card__amenity-chip">Fish freezer</span>
                    <span class="accommodation-card__amenity-chip">BBQ Area</span>
                    <span class="accommodation-card__amenity-chip">Parking spaces</span>
                    <span class="accommodation-card__amenity-chip">TV</span>
                    <span class="accommodation-card__amenity-chip">Terrace</span>
                    <span class="accommodation-card__amenity-chip">Keybox</span>
                    <span class="accommodation-card__amenity-chip">Heating</span>
                </div>
            </div>
        </div>
    </div>
</div>

