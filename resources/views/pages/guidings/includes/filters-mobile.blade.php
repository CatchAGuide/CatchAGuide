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
                    <div id="price-slider-mobile"></div>
                    <div class="price-display mt-2">
                        £ <span id="price-min-display-mobile">50</span> - £ <span id="price-max-display-mobile">4,000</span>
                    </div>
                    <input type="hidden" name="price_min" id="price_min_mobile">
                    <input type="hidden" name="price_max" id="price_max_mobile">
                </div>
            </div>
            <hr>
            
            {{-- Target Fish Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">Target Fish</h6>
                <div class="checkbox-group">
                    @foreach($alltargets as $index => $target)
                        @if(isset($targetFishCounts[$target->id]) && $targetFishCounts[$target->id] > 0)
                            <div class="form-check {{ $index >= 8 ? 'd-none extra-filter' : '' }}">
                                <input type="checkbox" 
                                       class="form-check-input mobile-filter-checkbox" 
                                       name="target_fish[]" 
                                       id="fish_mobile_{{ $target->id }}" 
                                       value="{{ $target->id }}"
                                       {{ in_array($target->id, request()->get('target_fish', [])) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between" for="fish_mobile_{{ $target->id }}">
                                    {{ app()->getLocale() == 'en' ? $target->name_en : $target->name }}
                                    <span class="count">({{ $targetFishCounts[$target->id] }})</span>
                                </label>
                            </div>
                        @endif
                    @endforeach
                    <button type="button" class="btn btn-link see-more w-100 text-center">See More</button>
                </div>
            </div>
            <hr>

            {{-- Methods Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">Methods</h6>
                <div class="checkbox-group">
                    @foreach($guiding_methods as $index => $method)
                        @if(isset($methodCounts[$method->id]) && $methodCounts[$method->id] > 0)
                            <div class="form-check {{ $index >= 8 ? 'd-none extra-filter' : '' }}">
                                <input type="checkbox" 
                                       class="form-check-input mobile-filter-checkbox" 
                                       name="methods[]" 
                                       id="method_mobile_{{ $method->id }}" 
                                       value="{{ $method->id }}"
                                       {{ in_array($method->id, request()->get('methods', [])) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between" for="method_mobile_{{ $method->id }}">
                                    {{ app()->getLocale() == 'en' ? $method->name_en : $method->name }}
                                    <span class="count">({{ $methodCounts[$method->id] }})</span>
                                </label>
                            </div>
                        @endif
                    @endforeach
                    <button type="button" class="btn btn-link see-more w-100 text-center">See More</button>
                </div>
            </div>

            {{-- Water Types Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">Water Types</h6>
                <div class="checkbox-group">
                    @foreach($guiding_waters as $index => $water)
                        @if(isset($waterTypeCounts[$water->id]) && $waterTypeCounts[$water->id] > 0)
                            <div class="form-check {{ $index >= 8 ? 'd-none extra-filter' : '' }}">
                                <input type="checkbox" 
                                       class="form-check-input mobile-filter-checkbox" 
                                       name="water[]" 
                                       id="water_mobile_{{ $water->id }}" 
                                       value="{{ $water->id }}"
                                       {{ in_array($water->id, request()->get('water', [])) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between" for="water_mobile_{{ $water->id }}">
                                    {{ app()->getLocale() == 'en' ? $water->name_en : $water->name }}
                                    <span class="count">({{ $waterTypeCounts[$water->id] }})</span>
                                </label>
                            </div>
                        @endif
                    @endforeach
                    <button type="button" class="btn btn-link see-more w-100 text-center">See More</button>
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
                        @if(isset($durationCounts[$durationType]) && $durationCounts[$durationType] > 0)
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input mobile-filter-checkbox" 
                                       name="duration_types[]" 
                                       id="duration_mobile_{{ $durationType }}" 
                                       value="{{ $durationType }}"
                                       {{ in_array($durationType, request()->get('duration_types', [])) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex justify-content-between" for="duration_mobile_{{ $durationType }}">
                                    {{ $label }}
                                    <span class="count">({{ $durationCounts[$durationType] }})</span>
                                </label>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Number of People Section --}}
            <div class="filter-section mb-4">
                <h6 class="mb-3">{{translate('Number of People')}}</h6>
                <div class="checkbox-group">
                    @foreach($personCounts as $persons => $count)
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input mobile-filter-checkbox" 
                                   name="num_persons[]" 
                                   id="persons_mobile_{{ $persons }}" 
                                   value="{{ $persons }}"
                                   {{ in_array((string)$persons, request()->get('num_persons', [])) ? 'checked' : '' }}>
                            <label class="form-check-label d-flex justify-content-between" for="persons_mobile_{{ $persons }}">
                                {{ translate('Up to') }} {{ $persons }} {{ translate('person'.($persons > 1 ? 's' : '')) }}
                                <span class="count">({{ $count }})</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    {{-- Sticky Bottom Button --}}
    <div class="offcanvas-footer border-top">
        <button type="submit" class="btn btn-primary w-100 py-3" form="filterContainerOffCanvas">
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
        text-align: center;
        margin-top: 1rem;
        font-weight: 500;
        color: #212529;
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
</style>
@endpush

@push('guidingListingScripts')
<script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterContainerOffCanvas');

        // Initialize price slider
        FilterManager.initPriceSlider(
            'price-slider-mobile',
            'price-min-display-mobile',
            'price-max-display-mobile',
            'price_min_mobile',
            'price_max_mobile',
            updateResults
        );

        // Initialize duration switch
        // FilterManager.initDurationSwitch('durationUnitSwitchMobile', 'durationUnitTextMobile', 'duration_mobile');

        // Add change event listener to all filter checkboxes
        document.querySelectorAll('.mobile-filter-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateResults(); // Call the existing updateResults function
            });
        });

        function updateResults() {
            const formData = new FormData(filterForm);
            const queryString = new URLSearchParams(formData).toString();
            const currentPath = window.location.pathname;

            // Add loading state
            const listingsContainer = document.querySelector('.tours-list__inner');
            if (listingsContainer) {
                listingsContainer.classList.add('loading');
            }

            fetch(`${currentPath}?${queryString}`, {
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

                // Store filtered guidings in window object
                window.filteredGuidings = data.guidings || [];

                if (listingsContainer) {
                    listingsContainer.innerHTML = data.html;
                    listingsContainer.classList.remove('loading');
                    reinitializeComponents();
                }
                
                if (data.searchMessage) {
                    updateSearchMessage(data.searchMessage, listingsContainer);
                }
                
                // Update URL without page reload
                const newUrl = `${currentPath}?${queryString}`;
                window.history.pushState({ path: newUrl }, '', newUrl);

                // Update the filter options based on available results
                FilterManager.updateFilters(data);

                window.updateMapWithGuidings(data.guidings);
            })
            .catch(error => {
                console.error('Error updating results:', error);
                if (listingsContainer) {
                    listingsContainer.classList.remove('loading');
                }
            });
        }

        function updateSearchMessage(message, listingsContainer) {
            const messageElement = document.querySelector('.alert.alert-info');
            if (messageElement) {
                messageElement.textContent = message;
            } else if (message.trim() !== '') {
                const newMessage = document.createElement('div');
                newMessage.className = 'alert alert-info mb-3';
                newMessage.role = 'alert';
                newMessage.textContent = message;
                listingsContainer.parentElement.insertBefore(newMessage, listingsContainer);
            }
        }

        function reinitializeComponents() {
            document.querySelectorAll('.carousel').forEach(carousel => {
                new bootstrap.Carousel(carousel, {
                    interval: false
                });
            });
            attachFilterRemoveListeners();
        }

        window.reinitializeComponents = reinitializeComponents;

        // Number input handlers for mobile
        window.incrementValue = function(id) {
            const input = document.getElementById(id);
            const newValue = parseInt(input.value || 0) + 1;
            input.value = newValue;
            input.dispatchEvent(new Event('change'));
        }

        window.decrementValue = function(id) {
            const input = document.getElementById(id);
            const minValue = parseFloat(input.min || 1);
            const currentValue = parseInt(input.value || 0);
            if (currentValue > minValue) {
                input.value = currentValue - 1;
                input.dispatchEvent(new Event('change'));
            }
        }

        // Add mobile inputs to the filter update logic
        const durationMobile = document.getElementById('duration_mobile');
        const peopleMobile = document.getElementById('people_mobile');
        
        if (durationMobile) {
            durationMobile.addEventListener('change', updateResults);
        }
        if (peopleMobile) {
            peopleMobile.addEventListener('change', updateResults);
        }
    });
</script>
@endpush