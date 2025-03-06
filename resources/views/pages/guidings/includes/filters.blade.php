<div class="card d-block d-none d-sm-block">
    <div class="card-header">
        @lang('destination.filter_by'):
    </div>
    <div class="card-body border-bottom">
        <form id="filterContainer" action="{{ $formAction }}" method="get" class="shadow-sm px-4 py-2">
            <input type="hidden" name="ismobile" value="{{ $agent->ismobile() }}">
            <div class="row">
                <div class="col-12 mb-2">
                    <div class="form-group my-1">
                        <h5 class="mb-2">{{translate('Your budget')}}</h5>
                        <div class="price-range-slider">
                            <div class="chart-container mb-2">
                                <canvas id="price-histogram"></canvas>
                            </div>
                            <div id="price-slider-main"></div>
                            <div class="price-display mt-2">
                                € <span id="price-min-display">50</span> - € <span id="price-max-display">4,000</span>
                            </div>
                            <input type="hidden" name="price_min" id="price_min_main">
                            <input type="hidden" name="price_max" id="price_max_main">
                        </div>
                    </div>
                </div>
                <hr>

                {{-- Target Fish --}}
                <div class="filter-section mb-3">
                    <div class="form-group mb-3">
                        <h5 class="mb-2">{{translate('Target Fish')}}</h5>
                        <div class="checkbox-group">
                            @php
                                $visibleCount = 0;
                                $totalCount = count($alltargets);
                                $maxInitialVisible = 7;
                                $checkedItems = [];
                                $uncheckedItems = [];
                                
                                // Separate checked and unchecked items
                                foreach($alltargets as $target) {
                                    if(in_array($target->id, request()->get('target_fish', []))) {
                                        $checkedItems[] = $target;
                                    } else {
                                        $uncheckedItems[] = $target;
                                    }
                                }
                                
                                // Combine them with checked items first
                                $sortedTargets = array_merge($checkedItems, $uncheckedItems);

                            @endphp
                            
                            @foreach($sortedTargets as $index => $target)
                                @php
                                    $isChecked = in_array($target->id, request()->get('target_fish', []));
                                    $shouldBeVisible = $visibleCount < $maxInitialVisible || $isChecked;
                                    if($shouldBeVisible) $visibleCount++;
                                @endphp
                                <div class="form-check {{ (!$shouldBeVisible) ? 'd-none extra-filter' : '' }}">
                                    <input type="checkbox" 
                                           class="form-check-input filter-checkbox" 
                                           name="target_fish[]" 
                                           id="fish_{{ $target->id }}" 
                                           value="{{ $target->id }}"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="fish_{{ $target->id }}">
                                        {{ app()->getLocale() == 'en' ? $target->name_en : $target->name }}
                                        <span class="count">({{ $targetFishCounts[$target->id] ?? 0 }})</span>
                                    </label>
                                </div>
                            @endforeach
                            @if($totalCount > $maxInitialVisible)
                                <button type="button" class="btn btn-link see-more">See More</button>
                            @endif
                        </div>
                    </div>
                </div>
                <hr>

                {{-- Methods --}}
                <div class="filter-section mb-3">
                    <div class="form-group mb-3">
                        <h5 class="mb-2">{{translate('Methods')}}</h5>
                        <div class="checkbox-group">
                            @php
                                $visibleCount = 0;
                                $totalCount = count($guiding_methods);
                                $maxInitialVisible = 7;
                                $checkedItems = [];
                                $uncheckedItems = [];
                                
                                // Separate checked and unchecked items
                                foreach($guiding_methods as $method) {
                                    if(in_array($method->id, request()->get('methods', []))) {
                                        $checkedItems[] = $method;
                                    } else {
                                        $uncheckedItems[] = $method;
                                    }
                                }
                                
                                // Combine them with checked items first
                                $sortedMethods = array_merge($checkedItems, $uncheckedItems);
                            @endphp
                            
                            @foreach($sortedMethods as $index => $method)
                                @php
                                    $isChecked = in_array($method->id, request()->get('methods', []));
                                    $shouldBeVisible = $visibleCount < $maxInitialVisible || $isChecked;
                                    if($shouldBeVisible) $visibleCount++;
                                @endphp
                                <div class="form-check {{ (!$shouldBeVisible) ? 'd-none extra-filter' : '' }}">
                                    <input type="checkbox" 
                                           class="form-check-input filter-checkbox" 
                                           name="methods[]" 
                                           id="method_{{ $method->id }}" 
                                           value="{{ $method->id }}"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="method_{{ $method->id }}">
                                        {{ app()->getLocale() == 'en' ? $method->name_en : $method->name }}
                                        <span class="count">({{ $methodCounts[$method->id] ?? 0 }})</span>
                                    </label>
                                </div>
                            @endforeach
                            @if($totalCount > $maxInitialVisible)
                                <button type="button" class="btn btn-link see-more">See More</button>
                            @endif
                        </div>
                    </div>
                </div>
                <hr>

                {{-- Water Types --}}
                <div class="filter-section mb-3">
                    <div class="form-group mb-3">
                        <h5 class="mb-2">{{translate('Water Types')}}</h5>
                        <div class="checkbox-group">
                            @php
                                $visibleCount = 0;
                                $totalCount = count($guiding_waters);
                                $maxInitialVisible = 7;
                                $checkedItems = [];
                                $uncheckedItems = [];
                                
                                // Separate checked and unchecked items
                                foreach($guiding_waters as $water) {
                                    if(in_array($water->id, request()->get('water', []))) {
                                        $checkedItems[] = $water;
                                    } else {
                                        $uncheckedItems[] = $water;
                                    }
                                }
                                
                                // Combine them with checked items first
                                $sortedWaters = array_merge($checkedItems, $uncheckedItems);
                            @endphp
                            
                            @foreach($sortedWaters as $index => $water)
                                @php
                                    $isChecked = in_array($water->id, request()->get('water', []));
                                    $shouldBeVisible = $visibleCount < $maxInitialVisible || $isChecked;
                                    if($shouldBeVisible) $visibleCount++;
                                @endphp
                                <div class="form-check {{ (!$shouldBeVisible) ? 'd-none extra-filter' : '' }}">
                                    <input type="checkbox" 
                                           class="form-check-input filter-checkbox" 
                                           name="water[]" 
                                           id="water_{{ $water->id }}" 
                                           value="{{ $water->id }}"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="water_{{ $water->id }}">
                                        {{ app()->getLocale() == 'en' ? $water->name_en : $water->name }}
                                        <span class="count">({{ $waterTypeCounts[$water->id] ?? 0 }})</span>
                                    </label>
                                </div>
                            @endforeach
                            @if($totalCount > $maxInitialVisible)
                                <button type="button" class="btn btn-link see-more">See More</button>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>
                <div class="filter-section mb-3">
                    <div class="form-group mb-3">
                        <h5 class="mb-2">{{translate('Duration')}}</h5>
                        <div class="checkbox-group">
                            @php
                                $durationLabels = [
                                    'half_day' => translate('Half Day'),
                                    'full_day' => translate('Full Day'),
                                    'multi_day' => translate('Multi Day')
                                ];
                            @endphp
                            @foreach($durationLabels as $durationType => $label)
                                <div class="form-check {{ $durationCounts[$durationType] > 0 ? '' : 'd-none' }}">
                                    <input type="checkbox" 
                                           class="form-check-input filter-checkbox" 
                                           name="duration_types[]" 
                                           id="duration_{{ $durationType }}" 
                                           value="{{ $durationType }}"
                                           {{ in_array($durationType, request()->get('duration_types', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="duration_{{ $durationType }}">
                                        {{ $label }}
                                        <span class="count">({{ $durationCounts[$durationType] ?? 0 }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <hr>
                <div class="filter-section mb-3">
                    <div class="form-group mb-3">
                        <h5 class="mb-2">{{translate('Number of People')}}</h5>
                        <div class="checkbox-group" id="person-checkbox-group">
                            @foreach($personCounts as $persons => $count)
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input filter-checkbox" 
                                           name="num_persons[]" 
                                           id="persons_{{ $persons }}" 
                                           value="{{ $persons }}"
                                           {{ in_array((string)$persons, request()->get('num_persons', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="persons_{{ $persons }}">
                                        {{ translate('Up to') }} {{ $persons }} {{ translate('person'.($persons > 1 ? 's' : '')) }}
                                        <span class="count">({{ $count }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('guidingListingStyles')
    <style>
    .price-range-slider {
        padding: 10px 20px;
        margin-bottom: 15px;
    }

    .price-display {
        font-size: 14px;
        color: #333;
        margin-bottom: 10px;
    }

    .noUi-connect {
        background: #E8604C;
    }

    .noUi-handle {
        border-radius: 3px;
        background: #fff;
        cursor: pointer;
        border: 1px solid #E8604C;
    }

    .noUi-horizontal {
        height: 8px;
    }

    .noUi-horizontal .noUi-handle {
        width: 20px;
        height: 20px;
        right: -10px;
        top: -7px;
    }

    .noUi-handle:before,
    .noUi-handle:after {
        display: none;
    }

    .price-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    .price-label {
        font-size: 14px;
        color: #666;
    }

    @media (max-width: 767px) {
        .price-range-slider {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .price-display {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .noUi-connect {
            background: #007AFF;  /* iOS-style blue */
        }

        .noUi-handle {
            border-color: #007AFF;
            box-shadow: none;
        }
    }

    .filter-options .form-check {
        margin-bottom: 0.5rem;
    }

    .filter-options .badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        border: 1px solid #dee2e6;
    }

    .filter-options .form-check-label {
        margin-right: 0.5rem;
    }

    .loading {
        position: relative;
        opacity: 0.6;
        pointer-events: none;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 2rem;
        height: 2rem;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #E8604C;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translate(-50%, -50%);
    }

    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }

    .checkbox-group {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px;
        position: relative;
    }

    .extra-filter {
        display: none;
    }

    .extra-filter.show {
        display: block !important; /* Override any inline styles */
    }

    .see-more {
        position: sticky;
        bottom: 0;
        width: 100%;
        background: white;
        border-top: 1px solid #dee2e6;
        margin-top: 8px;
        padding-top: 8px;
    }

    .form-check {
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .form-check:last-child {
        margin-bottom: 0;
    }

    .form-check-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        cursor: pointer;
    }

    .count {
        color: #666;
        font-size: 0.9em;
        margin-left: 8px;
    }

    .form-check-input:checked + .form-check-label {
        color: #E8604C;
    }

    .text-muted.small {
        padding: 8px;
        text-align: center;
    }

    .active-filters .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5em 0.7em;
        font-weight: normal;
    }

    .active-filters .btn-close {
        padding: 0.25em;
        font-size: 0.75em;
        opacity: 0.5;
    }

    .active-filters .btn-close:hover {
        opacity: 1;
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

    .chart-container {
        width: 100%;
        height: 80px;
        margin-bottom: 10px;
    }
    </style>
    
@endpush

@push('guidingListingScripts')
<script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/filters-manager.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterContainer');

        // Initialize price slider with histogram
        FilterManager.initPriceSlider(
            'price-slider-main',
            'price-min-display',
            'price-max-display',
            'price_min_main',
            'price_max_main',
            updateResults,
            'price-histogram'  // Add histogram canvas ID
        );

        // Initialize see more buttons
        FilterManager.initSeeMoreButtons();

        // Add change event listener to all filter checkboxes
        document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateResults(); // Call the existing updateResults function
            });
        });

        function updateResults() {
            // Show loading overlay
            FilterManager.showLoadingOverlay();
            
            const currentPath = window.location.pathname;
            const form = document.getElementById('filterContainer');
            const formData = new FormData(form);
            
            const queryString = new URLSearchParams();
            
            // Add each parameter to URLSearchParams properly
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
                // Update the listings container with the new HTML
                const listingsContainer = document.querySelector('#guidings-list');
                if (listingsContainer) {
                    listingsContainer.innerHTML = data.html;
                    reinitializeComponents();
                }
                
                if (data.searchMessage) {
                    updateSearchMessage(data.searchMessage, listingsContainer);
                }
                
                // Update URL without page reload
                const newUrl = `${currentPath}?${queryString.toString()}`;
                window.history.pushState({ path: newUrl }, '', newUrl);
                
                // Update the filter options based on available results
                FilterManager.updateFilters(data);
                
                if (typeof window.updateMapWithGuidings === 'function') {
                    window.updateMapWithGuidings(data.guidings);
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

        function updateSearchMessage(message, container) {
            let searchMessageElement = document.querySelector('.alert.alert-info');
            
            if (!message && searchMessageElement) {
                searchMessageElement.remove();
                return;
            }
            
            if (!message) return;
            
            if (!searchMessageElement) {
                searchMessageElement = document.createElement('div');
                searchMessageElement.className = 'alert alert-info mb-3';
                searchMessageElement.setAttribute('role', 'alert');
                
                // Insert before the listings container
                const parent = container.parentNode;
                parent.insertBefore(searchMessageElement, container);
            }
            
            searchMessageElement.textContent = message;
        }

        // Handle active filter removal
        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-filter-type]') || e.target.closest('[data-filter-type]')) {
                const button = e.target.matches('[data-filter-type]') ? e.target : e.target.closest('[data-filter-type]');
                const filterType = button.dataset.filterType;
                const filterId = button.dataset.filterId;
                
                // Find and uncheck the corresponding checkbox
                const checkbox = document.querySelector(`input[name="${filterType}[]"][value="${filterId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    updateResults();
                }
            }
        });

        // Initialize components that need to be reinitialized after AJAX updates
        function reinitializeComponents() {
            // Re-attach event listeners to filter removal buttons
            document.querySelectorAll('[data-filter-type]').forEach(button => {
                button.addEventListener('click', function() {
                    const filterType = this.dataset.filterType;
                    const filterId = this.dataset.filterId;
                    
                    // Find and uncheck the corresponding checkbox
                    const checkbox = document.querySelector(`input[name="${filterType}[]"][value="${filterId}"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                        updateResults();
                    }
                });
            });
            
            // Re-initialize carousels
            document.querySelectorAll('.carousel').forEach(carousel => {
                new bootstrap.Carousel(carousel, {
                    interval: false
                });
            });
        }

        // Initialize components on page load
        reinitializeComponents();
    });
</script>
@endpush