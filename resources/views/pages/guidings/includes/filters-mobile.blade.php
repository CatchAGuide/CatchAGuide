<div class="offcanvas offcanvas-bottom h-100" tabindex="-1" id="offcanvasBottomSearch" aria-labelledby="offcanvasBottomLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvasBottomLabel">Filters</h5>
        <div>
            <button type="button" class="btn btn-link" id="clearAllFiltersMobile">Clear</button>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body small">
        <form id="filterContainerOffCanvas" action="{{ $formAction }}" method="get">
            <input type="hidden" name="ismobile" value="1">
            
            {{-- Price Range Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">{{translate('Your budget')}}</h6>
                <div class="price-range-slider px-3">
                    <div class="chart-container mb-2">
                        <canvas id="price-histogram-mobile"></canvas>
                    </div>
                    <div id="price-slider-mobile"></div>
                    <input type="hidden" name="price_min" id="price_min_mobile">
                    <input type="hidden" name="price_max" id="price_max_mobile">
                </div>
            </div>
            <hr>
            
            {{-- Target Fish Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">Target Fish</h6>
                <div class="checkbox-group">
                    @php
                        $visibleCount = 0;
                        $totalCountTargets = count($alltargets);
                        $maxInitialVisible = 7;
                        $locale = app()->getLocale();
                        
                        $sortedTargets = $alltargets->sortBy(function($target) use ($locale) {
                            return $locale == 'en' ? $target->name_en : $target->name;
                        });
                    @endphp
                    
                    @foreach($sortedTargets as $index => $target)
                        @php
                            $isChecked = in_array($target->id, request()->get('target_fish', []));
                            $shouldBeVisible = $visibleCount < $maxInitialVisible || $isChecked;
                            if($shouldBeVisible) $visibleCount++;
                        @endphp
                        <div class="form-check {{ (!$shouldBeVisible) ? 'd-none extra-filter' : '' }}">
                            <input type="checkbox" 
                                   class="form-check-input mobile-filter-checkbox" 
                                   name="target_fish[]" 
                                   id="fish_mobile_{{ $target->id }}" 
                                   value="{{ $target->id }}"
                                   {{ $isChecked ? 'checked' : '' }}>
                            <label class="form-check-label d-flex justify-content-between" for="fish_mobile_{{ $target->id }}">
                                {{ app()->getLocale() == 'en' ? $target->name_en : $target->name }}
                                <span class="count">({{ $targetFishCounts[$target->id] ?? 0 }})</span>
                            </label>
                        </div>
                    @endforeach
                    @if($totalCountTargets > $maxInitialVisible)
                        <button type="button" class="btn btn-link see-more w-100 text-center">See More</button>
                    @endif
                </div>
            </div>
            <hr>

            {{-- Methods Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">Methods</h6>
                <div class="checkbox-group">
                    @php
                        $totalCountGuidings = count($guiding_methods);
                        $sortedMethods = $guiding_methods->sortBy(function($method) use ($locale) {
                            return $locale == 'en' ? $method->name_en : $method->name;
                        });
                    @endphp
                    
                    @foreach($sortedMethods as $index => $method)
                        @php
                            $isChecked = in_array($method->id, request()->get('methods', []));
                            $shouldBeVisible = $visibleCount < $maxInitialVisible || $isChecked;
                            if($shouldBeVisible) $visibleCount++;
                        @endphp
                        <div class="form-check {{ (!$shouldBeVisible) ? 'd-none extra-filter' : '' }}">
                            <input type="checkbox" 
                                   class="form-check-input mobile-filter-checkbox" 
                                   name="methods[]" 
                                   id="method_mobile_{{ $method->id }}" 
                                   value="{{ $method->id }}"
                                   {{ $isChecked ? 'checked' : '' }}>
                            <label class="form-check-label d-flex justify-content-between" for="method_mobile_{{ $method->id }}">
                                {{ app()->getLocale() == 'en' ? $method->name_en : $method->name }}
                                <span class="count">({{ $methodCounts[$method->id] ?? 0 }})</span>
                            </label>
                        </div>
                    @endforeach
                    @if($totalCountGuidings > $maxInitialVisible)
                        <button type="button" class="btn btn-link see-more w-100 text-center">See More</button>
                    @endif
                </div>
            </div>

            {{-- Water Types Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">Water Types</h6>
                <div class="checkbox-group">
                    @php
                        $totalCountWaters = count($guiding_waters);
                        
                        $sortedWaters = $guiding_waters->sortBy(function($water) use ($locale) {
                            return $locale == 'en' ? $water->name_en : $water->name;
                        });
                    @endphp
                    
                    @foreach($sortedWaters as $index => $water)
                        @php
                            $isChecked = in_array($water->id, request()->get('water', []));
                            $shouldBeVisible = $visibleCount < $maxInitialVisible || $isChecked;
                            if($shouldBeVisible) $visibleCount++;
                        @endphp
                        <div class="form-check {{ (!$shouldBeVisible) ? 'd-none extra-filter' : '' }}">
                            <input type="checkbox" 
                                   class="form-check-input mobile-filter-checkbox" 
                                   name="water[]" 
                                   id="water_mobile_{{ $water->id }}" 
                                   value="{{ $water->id }}"
                                   {{ $isChecked ? 'checked' : '' }}>
                            <label class="form-check-label d-flex justify-content-between" for="water_mobile_{{ $water->id }}">
                                {{ app()->getLocale() == 'en' ? $water->name_en : $water->name }}
                                <span class="count">({{ $waterTypeCounts[$water->id] ?? 0 }})</span>
                            </label>
                        </div>
                    @endforeach
                    @if($totalCountWaters > $maxInitialVisible)
                        <button type="button" class="btn btn-link see-more w-100 text-center">See More</button>
                    @endif
                </div>
            </div>
            
            <hr>
            <div class="filter-section mb-4">
                <h6 class="mb-3">{{translate('Duration')}}</h6>
                <div class="checkbox-group">
                    @php
                        $durationLabels = [
                            'half_day' => translate('Half Day'),
                            'full_day' => translate('Full Day'),
                            'multi_day' => translate('Multi Day')
                        ];
                    @endphp
                    @foreach($durationLabels as $durationType => $label)
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input mobile-filter-checkbox" 
                                   name="duration_types[]" 
                                   id="duration_mobile_{{ $durationType }}" 
                                   value="{{ $durationType }}"
                                   {{ in_array($durationType, request()->get('duration_types', [])) ? 'checked' : '' }}>
                            <label class="form-check-label d-flex justify-content-between" for="duration_mobile_{{ $durationType }}">
                                {{ $label }}
                                <span class="count">({{ $durationCounts[$durationType] ?? 0 }})</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Number of People Section --}}
            <div class="filter-section mb-3">
                <div class="form-group mb-3">
                    <h5 class="mb-2">{{translate('Number of People')}}</h5>
                    <div class="checkbox-group" id="mobile-person-checkbox-group">
                        @foreach($personCounts as $persons => $count)
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input mobile-filter-checkbox" 
                                       name="num_persons" 
                                       id="mobile_persons_{{ $persons }}" 
                                       value="{{ $persons }}"
                                       {{ request()->get('num_persons') == (string)$persons ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between" for="mobile_persons_{{ $persons }}">
                                    {{ translate('Up to') }} {{ $persons }} {{ translate('person'.($persons > 1 ? 's' : '')) }}
                                    <span class="count">({{ $count }})</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Sticky Bottom Button --}}
    <div class="offcanvas-footer border-top">
        <button type="submit" class="btn btn-primary w-100 py-3" form="filterContainerOffCanvas" id="mobileShowResultsBtn">
            Show <span id="mobileResultsCount">{{ $guidings->total() }}</span> results
        </button>
    </div>
</div>

@push('guidingListingStyles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.css">
<style>
#offcanvasBottomSearch {
    /* Ensure proper modal positioning and structure */
    position: fixed;
    bottom: 0;
    display: flex;
    flex-direction: column;
    height: 100vh !important; /* Force full height */
    max-height: 100vh;
    width: 100%;
    margin: 0;
    
    .offcanvas-header {
        flex-shrink: 0; /* Prevent header from shrinking */
    }

    .offcanvas-body {
        flex: 1 1 auto; /* Allow body to grow and shrink */
        overflow-y: auto;
        padding: 1rem;
        height: 0; /* This forces the body to scroll properly */
    }

    .offcanvas-footer {
        flex-shrink: 0; /* Prevent footer from shrinking */
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
        z-index: 1030; /* Ensure it stays above other content */
    }

    .checkbox-group {
        position: relative;
        overflow-y: visible;
        padding: 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .filter-section {
        padding: 0 1rem;
        margin-bottom: 1.5rem;

        h6 {
            font-weight: 600;
        }
    }

    .form-check {
        margin-bottom: 0.75rem;
        padding: 0.25rem 0;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .form-check-label {
        font-size: 0.9375rem;
        color: #212529;
    }

    .count {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .see-more {
        width: 100%;
        text-align: center;
        padding: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px solid #dee2e6;
        background: none;
        border: none;
        color: #0d6efd;
        cursor: pointer;
    }

    .see-more:hover {
        color: #0a58ca;
        text-decoration: underline;
    }

    .extra-filter {
        display: none;
    }

    .extra-filter.show {
        display: block !important;
    }

    /* Price slider styles */
    .price-range-slider {
        padding: 1rem;
        background: #fff;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        margin: 0 1rem; /* Add horizontal margin */
    }

    .price-display {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: #333;
        margin-bottom: 10px;
    }

    .price-label {
        font-size: 14px;
        color: #666;
    }

    .noUi-connect {
        background: #0d6efd;
    }

    .noUi-horizontal {
        height: 8px;
    }

    .noUi-handle {
        border-radius: 50%;
        background: #fff;
        border: 2px solid #0d6efd;
        box-shadow: none;
        width: 20px !important;
        height: 20px !important;
        right: -10px !important;
        top: -7px !important;

        &:before,
        &:after {
            display: none;
        }
    }

    /* Loading state */
    .loading {
        opacity: 0.6;
        pointer-events: none;
        position: relative;

        &:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2rem;
            height: 2rem;
            margin: -1rem 0 0 -1rem;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    }

    .number-input,
    .duration-input {
        max-width: 200px;
        margin: 0 auto;
    }

    .number-input input[type="number"],
    .duration-input input[type="number"] {
        text-align: center;
        padding: 0.375rem 0.75rem;
    }

    .number-input button,
    .duration-input button {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .number-input input[type="number"]::-webkit-inner-spin-button,
    .number-input input[type="number"]::-webkit-outer-spin-button,
    .duration-input input[type="number"]::-webkit-inner-spin-button,
    .duration-input input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .form-check-input:checked {
        background-color: #E8604C;
        border-color: #E8604C;
    }

    .form-switch .form-check-input {
        width: 2.5em;
    }

    .form-check-label {
        font-size: 0.9rem;
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Ensure proper modal animation */
.offcanvas-bottom.show {
    transform: none !important;
}

/* Loading overlay styles */
#filter-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity 0.3s ease;
}

.tours-list__inner.loading {
    opacity: 0.5;
    pointer-events: none;
}
</style>
@endpush

@push('guidingListingScripts')
<script>
    // Pass the price histogram data to JavaScript (if not already passed in filters.blade.php)
    window.priceHistogramData = window.priceHistogramData || {!! json_encode($priceHistogramData) !!};
    window.maxPrice = window.maxPrice || {!! json_encode($overallMaxPrice) !!};
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize price slider for mobile with histogram
        window.priceSliderMobile = FilterManager.initPriceSlider(
            'price-slider-mobile',
            'price-min-display-mobile',
            'price-max-display-mobile',
            'price_min_mobile',
            'price_max_mobile',
            updateResults,
            'price-histogram-mobile'  // Add histogram canvas ID
        );

        // Add change event listener to all filter checkboxes
        document.querySelectorAll('.mobile-filter-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateResults(); // Call the existing updateResults function
            });
        });

        // Modified updateResults function to handle filter removal
        function updateResults(isFilterRemoval = false) {
            // Show loading overlay
            FilterManager.showLoadingOverlay();
            
            const currentPath = window.location.pathname;
            const form = document.getElementById('filterContainerOffCanvas');
            const formData = new FormData(form);
            
            // Get existing URL parameters that aren't in the form
            const currentUrlParams = new URLSearchParams(window.location.search);
            const queryString = new URLSearchParams();
            
            // First, preserve any existing URL parameters that aren't in the form
            for (const [key, value] of currentUrlParams.entries()) {
                // Skip parameters that will be set by the form
                const formElement = form.elements[key] || form.elements[key + '[]'];
                if (!formElement) {
                    queryString.append(key, value);
                }
            }
            
            // Add each form parameter to URLSearchParams properly
            for (const [key, value] of formData.entries()) {
                // Handle array parameters correctly
                if (key.endsWith('[]')) {
                    queryString.append(key, value);
                } else {
                    queryString.append(key, value);
                }
            }
            
            fetch(`${currentPath}?${queryString.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update mobile results count
                const mobileResultsCount = document.getElementById('mobileResultsCount');
                if (mobileResultsCount && data.total) {
                    mobileResultsCount.textContent = data.total;
                }
                
                const searchMessageTitle = document.getElementById('search-message-title');
                if (searchMessageTitle) {
                    const locationText = searchMessageTitle.textContent.split(/\d+/)[0].trim().replace('$countReplace total', '');
                    searchMessageTitle.textContent = `${locationText} ${data.totalCount}`;
                }

                // Update the listings container with the new HTML
                const listingsContainer = document.querySelector('#guidings-list');
                if (listingsContainer) {
                    listingsContainer.innerHTML = data.html;
                    
                    // Reinitialize components after updating HTML
                    reinitializeComponents();
                }
                
                if (data.searchMessage) {
                    let searchMessageElement = document.querySelector('.alert.alert-info');
                    
                    if (!data.searchMessage && searchMessageElement) {
                        searchMessageElement.remove();
                        return;
                    }
                    
                    if (!data.searchMessage) return;
                    
                    if (!searchMessageElement) {
                        searchMessageElement = document.createElement('div');
                        searchMessageElement.className = 'alert alert-info mb-3';
                        searchMessageElement.setAttribute('role', 'alert');
                        
                        // Insert before the listings container
                        const parent = listingsContainer.parentNode;
                        parent.insertBefore(searchMessageElement, listingsContainer);
                    }
                    
                    searchMessageElement.textContent = data.searchMessage;
                }
                
                // Update URL without page reload
                const newUrl = `${currentPath}?${queryString.toString()}`;
                window.history.pushState({ path: newUrl }, '', newUrl);
                
                // Update the filter options based on available results
                FilterManager.updateFilters(data);
                
                // Update map if it exists
                if (typeof window.updateMapWithGuidings === 'function') {
                    window.updateMapWithGuidings(data.allGuidings);
                }
                
                // Hide loading overlay
                FilterManager.hideLoadingOverlay();
            
            })
            .catch(error => {
                console.error('Error updating results:', error);
                // Hide loading overlay even on error
                FilterManager.hideLoadingOverlay();
            });
        }

        // Add this function to reinitialize components
        function reinitializeComponents() {
            // Re-attach event listeners to filter removal buttons
            FilterManager.attachFilterRemoveListeners();
            
            // Re-initialize carousels
            document.querySelectorAll('.carousel').forEach(carousel => {
                new bootstrap.Carousel(carousel, {
                    interval: false
                });
            });
        }

        // Add clear filters functionality
        document.getElementById('clearAllFiltersMobile').addEventListener('click', function() {
            // Clear all checkboxes
            document.querySelectorAll('.mobile-filter-checkbox[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Clear all radio buttons
            document.querySelectorAll('.mobile-filter-checkbox[type="radio"]').forEach(radio => {
                radio.checked = false;
            });
            
            // Reset price slider to default values
            if (window.priceSliderMobile) { 
                window.priceSliderMobile.set([50, window.maxPrice > 4000 ? window.maxPrice : 4000]);
                // document.getElementById('price-min-display-mobile').textContent = '50';
                // document.getElementById('price-max-display-mobile').textContent = window.maxPrice > 4000 ? window.maxPrice : 4000;
                document.getElementById('price_min_mobile').value = '50';
                document.getElementById('price_max_mobile').value = window.maxPrice > 4000 ? window.maxPrice : 4000;
            }
            
            // Trigger update to refresh results
            updateResults(true); // Pass true to indicate filter removal
            
            if (typeof updateActiveFilterCounter === 'function') {
                updateActiveFilterCounter();
            }
        });

        // Make updateResults function available globally
        window.updateResults = updateResults;
    });
</script>
@endpush