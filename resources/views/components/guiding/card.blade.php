<div x-data="guidingCard(@js($guiding))" class="guiding-card">
    <div class="guiding-card__grid">
        <!-- Left Column: Image + Inclusives -->
        <div class="guiding-card__left-column">
            <!-- Gallery -->
            <div class="guiding-card__gallery">
                <img :src="currentImage || 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop'" :alt="guiding.title || 'Guiding'" />
            </div>
            
            <!-- Inclusives Section below Image -->
            <div class="guiding-card__inclusives-box">
                <div class="guiding-card__inclusives-title">Inklusive</div>
                <div class="guiding-card__inclusives-chips">
                    <span class="guiding-card__inclusive-chip">
                        <svg class="guiding-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span x-text="guiding.inclusives?.[0] || 'Spottransfer'">Spottransfer</span>
                    </span>
                    <span class="guiding-card__inclusive-chip">
                        <svg class="guiding-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span x-text="guiding.inclusives?.[1] || 'Signalhorn'">Signalhorn</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Column: Content -->
        <div class="guiding-card__right-column">
            <!-- Row 1: Location | Price Label -->
            <div class="guiding-card__info-grid">
                <div class="guiding-card__location">
                    <svg class="guiding-card__location-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="guiding-card__location-text" x-text="guiding.location || 'Location'">Location</span>
                </div>
                <div class="guiding-card__price-label" x-text="guiding.price?.type || 'Preis pro Tour'">Preis pro Tour</div>
            </div>

            <!-- Row 2: Title | Price Amount -->
            <div class="guiding-card__info-grid">
                <h3 class="guiding-card__title" x-text="guiding.title">Guiding Title</h3>
                <div class="guiding-card__price-amount" x-text="guiding.price?.amount ? fmt(guiding.price.amount, guiding.price.currency || 'EUR') : '€260.00'">260,00 €</div>
            </div>

            <!-- Row 3: Description | Button -->
            <div class="guiding-card__info-grid">
                <p class="guiding-card__description" x-text="guiding.description">Description</p>
                <button class="guiding-card__select-btn">
                    Dieses Guiding übernehmen
                </button>
            </div>

            <!-- Information Pills with minimal icons -->
            <div class="guiding-card__info-pills">
                <span class="guiding-card__info-pill">
                    <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span x-text="guiding.duration_hours || '6'">6</span> h
                </span>
                <span class="guiding-card__info-pill">
                    <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span x-text="guiding.max_persons || '3'">3</span> Pers
                </span>
                <span class="guiding-card__info-pill">
                    <svg class="guiding-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 6s1.5-2 5-2 5 2 5 2 1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2-1.5-2-5-2-5 2-5 2V6z"/>
                    </svg>
                    <span x-text="guiding.type || 'Ufer'">Ufer</span>
                </span>
            </div>

            <!-- Information Panels -->
            <div class="guiding-card__info-panels">
                <!-- Panel 1: Guiding Information -->
                <div class="guiding-card__panel">
                    <div class="guiding-card__panel-title">Guiding-Informationen</div>
                    <div class="guiding-card__panel-content">
                        <div>Art: <strong x-text="guiding.guiding_info?.art || 'Ufer'">Ufer</strong></div>
                        <div>Dauer: <strong x-text="guiding.guiding_info?.dauer || '6 h'">6 h</strong></div>
                        <div>Max. Personen: <strong x-text="guiding.guiding_info?.max_personen || '3'">3</strong></div>
                        <div>Gewässer: <strong x-text="guiding.guiding_info?.gewaesser || 'Stausee / Uferzonen'">Stausee / Uferzonen</strong></div>
                    </div>
                </div>

                <!-- Panel 2: Target Fish & Methods -->
                <div class="guiding-card__panel">
                    <div class="guiding-card__panel-title">Zielfische</div>
                    <div class="guiding-card__target-fish-chip">
                        <svg class="guiding-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-1 3.46-3.44 6-7 6s-7.56-2.54-8.5-6z"/>
                            <path d="M18 5L22 9M18 19L22 15M6 9L2 5M6 15L2 19"/>
                        </svg>
                        <span x-text="guiding.target_fish?.[0] || 'Wels'">Wels</span>
                    </div>
                    <div class="guiding-card__panel-title" style="margin-top: 12px;">Methoden</div>
                    <div class="guiding-card__methods-chips">
                        <span class="guiding-card__method-chip" x-text="guiding.methods?.[0] || 'U-Pose'">U-Pose</span>
                        <span class="guiding-card__method-chip" x-text="guiding.methods?.[1] || 'Boje'">Boje</span>
                        <span class="guiding-card__method-chip" x-text="guiding.methods?.[2] || 'Grundmontage'">Grundmontage</span>
                    </div>
                </div>

                <!-- Panel 3: Location & Notes -->
                <div class="guiding-card__panel">
                    <div class="guiding-card__panel-title">Ort & Hinweise</div>
                    <div class="guiding-card__panel-content">
                        <div>Treffpunkt: <strong x-text="guiding.meeting_point || 'Bucht Nord - Riba Roja'">Bucht Nord - Riba Roja</strong></div>
                        <div style="margin-top: 8px;">Startzeiten:</div>
                        <div class="guiding-card__start-times-chips">
                            <span class="guiding-card__start-time-chip">
                                <svg class="guiding-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5"/>
                                    <line x1="12" y1="1" x2="12" y2="3"/>
                                    <line x1="12" y1="21" x2="12" y2="23"/>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                    <line x1="1" y1="12" x2="3" y2="12"/>
                                    <line x1="21" y1="12" x2="23" y2="12"/>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                </svg>
                                <span x-text="guiding.start_times?.[0] || 'abends'">abends</span>
                            </span>
                            <span class="guiding-card__start-time-chip">
                                <svg class="guiding-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                </svg>
                                <span x-text="guiding.start_times?.[1] || 'nachts'">nachts</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

