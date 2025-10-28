<div x-data="boatCard(@js($boat))" class="rental-boat-card">
    <div class="rental-boat-card__grid">
        <!-- Left Column: Image + Inclusives & Extras -->
        <div class="rental-boat-card__left-column">
            <!-- Gallery -->
            <div class="rental-boat-card__gallery">
                <img :src="currentImage || 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop'" :alt="boat.title || 'Boat'" />
            </div>
            
            <!-- Inclusives & Extras Section -->
            <div class="rental-boat-card__inclusives-box">
                <div class="rental-boat-card__inclusives-title">Inklusive & Extras</div>
                
                <div style="margin-bottom: 12px;">
                    <div class="rental-boat-card__inclusives-subtitle">Inklusive</div>
                    <div class="rental-boat-card__inclusives-list">
                        <span class="rental-boat-card__inclusive-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Sicherheitsunterweisung</span>
                        </span>
                        <span class="rental-boat-card__inclusive-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Anker</span>
                        </span>
                        <span class="rental-boat-card__inclusive-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Signalhorn</span>
                        </span>
                        <span class="rental-boat-card__inclusive-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Erste Hilfe Set</span>
                        </span>
                        <span class="rental-boat-card__inclusive-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Schwimmweste</span>
                        </span>
                    </div>
                </div>
                
                <div>
                    <div class="rental-boat-card__inclusives-subtitle">Extras</div>
                    <div class="rental-boat-card__inclusives-chips">
                        <span class="rental-boat-card__inclusive-chip">
                            <svg class="rental-boat-card__check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Dry Bag</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Content -->
        <div class="rental-boat-card__right-column">
            <!-- Row 1: Location | Price Type -->
            <div class="rental-boat-card__info-grid">
                <div class="rental-boat-card__location">
                    <svg class="rental-boat-card__location-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="rental-boat-card__location-text" x-text="boat.location || 'Location'">Location</span>
                </div>
                <div class="rental-boat-card__price-label" x-text="boat.price?.type || 'pro Tag'">pro Tag</div>
            </div>

            <!-- Row 2: Title | Price Amount -->
            <div class="rental-boat-card__info-grid">
                <h3 class="rental-boat-card__title" x-text="boat.title">Boat Title</h3>
                <div class="rental-boat-card__price-amount" x-text="boat.price?.amount ? fmt(boat.price.amount, boat.price.currency || 'EUR') : '€25.00'">25,00 €</div>
            </div>

            <!-- Row 3: Type + Description | Button -->
            <div class="rental-boat-card__info-grid">
                <div>
                    <div class="rental-boat-card__type" x-text="boat.type">Boat Type</div>
                    <p class="rental-boat-card__description" x-text="boat.description">Description</p>
                </div>
                <button class="rental-boat-card__select-btn">
                    Dieses Boot übernehmen
                </button>
            </div>

            <!-- Information Pills -->
            <div class="rental-boat-card__info-pills">
                <span class="rental-boat-card__info-pill">
                    <svg class="rental-boat-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span x-text="boat.seats || '1'">1</span> Sitze
                </span>
                <span class="rental-boat-card__info-pill">
                    <svg class="rental-boat-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                    <span x-text="boat.length_m || '3.6'">3.6</span> m
                </span>
                <span class="rental-boat-card__info-pill">
                    <svg class="rental-boat-card__pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                    <span x-text="boat.width_m || '0.9'">0.9</span> m breit
                </span>
            </div>

            <!-- Information Panels -->
            <div class="rental-boat-card__info-panels">
                <!-- Panel 1: Boat Information -->
                <div class="rental-boat-card__panel">
                    <div class="rental-boat-card__panel-title">Bootsinformationen</div>
                    <div class="rental-boat-card__panel-content">
                        <div>Sitzplätze: <strong x-text="boat.seats || '1'">1</strong></div>
                        <div>Länge: <strong x-text="boat.length_m + ' m' || '3.6 m'">3.6 m</strong></div>
                        <div>Breite: <strong x-text="boat.width_m + ' m' || '0.9 m'">0.9 m</strong></div>
                        <div>Baujahr: <strong x-text="boat.year_built || '2022'">2022</strong></div>
                        <div>Boot: <strong x-text="boat.manufacturer || 'Hobie'">Hobie</strong></div>
                        <div>Motor: <strong x-text="boat.engine || '-'">-</strong></div>
                        <div>Leistung: <strong x-text="boat.power || '-'">-</strong></div>
                        <div>Vmax: <strong x-text="boat.max_speed_kmh + ' km/h' || '8 km/h'">8 km/h</strong></div>
                    </div>
                </div>

                <!-- Panel 2: Equipment -->
                <div class="rental-boat-card__panel">
                    <div class="rental-boat-card__panel-title">Ausstattung</div>
                    <div class="rental-boat-card__equipment-chips">
                        <span class="rental-boat-card__equipment-chip">
                            <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="2"/>
                                <path d="M12 1v6m0 6v6m-6-6h6m6 0h-6"/>
                            </svg>
                            Rutenhalter
                        </span>
                        <span class="rental-boat-card__equipment-chip">
                            <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20m0-4c-2 0-4-2-4-4s2-4 4-4m0 8c2 0 4-2 4-4s-2-4-4-4"/>
                            </svg>
                            Anker
                        </span>
                        <span class="rental-boat-card__equipment-chip">
                            <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            Signalhorn
                        </span>
                        <span class="rental-boat-card__equipment-chip">
                            <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <polyline points="12 22 12 12"/>
                            </svg>
                            Erste Hilfe
                        </span>
                        <span class="rental-boat-card__equipment-chip">
                            <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                            Schwimmwesten
                        </span>
                        <span class="rental-boat-card__equipment-chip">
                            <svg class="rental-boat-card__chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="2" x2="12" y2="22"/>
                                <polyline points="5 9 12 2 19 9"/>
                            </svg>
                            Ruder
                        </span>
                    </div>
                </div>

                <!-- Panel 3: Requirements -->
                <div class="rental-boat-card__panel">
                    <div class="rental-boat-card__panel-title">Anforderungen</div>
                    <div class="rental-boat-card__requirements-chips">
                        <span class="rental-boat-card__requirement-chip">Führerschein nicht nötig</span>
                        <span class="rental-boat-card__requirement-chip">Mindestalter 14</span>
                        <span class="rental-boat-card__requirement-chip">Ausweis mitbringen</span>
                        <span class="rental-boat-card__requirement-chip">Kaution nein</span>
                        <span class="rental-boat-card__requirement-chip">Sicherheitsunterweisung</span>
                        <span class="rental-boat-card__requirement-chip">Schwimmwestenpflicht</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
