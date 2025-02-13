<div class="card d-block d-none d-sm-block mb-1">
    <div class="card-header">
        @lang('message.sortby'):
    </div>
    <div class="card-body border-bottom">
        <form id="form-sortby-2" action="{{route('guidings.index')}}" method="get">
            <select class="form-select form-select-sm" name="sortby" id="sortby-2">
                <option value="" disabled selected>@lang('message.choose')...</option>
                <option value="newest" {{request()->get('sortby') == 'newest' ? 'selected' : '' }}>@lang('message.newest')</option>
                <option value="price-asc" {{request()->get('sortby') == 'price-asc' ? 'selected' : '' }}>@lang('message.lowprice')</option>
                <option value="short-duration" {{request()->get('sortby') == 'short-duration' ? 'selected' : '' }}>@lang('message.shortduration')</option>
                <option value="long-duration" {{request()->get('sortby') == 'long-duration' ? 'selected' : '' }}>@lang('message.longduration')</option>
            </select>

            @foreach(request()->except('sortby') as $key => $value)
                @if(is_array($value))
                    @foreach($value as $arrayValue)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $arrayValue }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
        </form>
    </div>
</div>

<div class="card d-block d-none d-sm-block">
    <div class="card-header">
        @lang('destination.filter_by'):
    </div>
    <div class="card-body border-bottom">
        <form id="filterContainer" action="{{ $formAction }}" method="get" class="shadow-sm px-4 py-2">
            <div class="row">

                <div class="col-12 mb-2">
                    <div class="form-group my-1">
                        <h5 class="mb-2">{{translate('Your budget')}}</h5>
                        <div class="price-range-slider">
                            <div id="price-slider-main"></div>
                            <div class="price-display mt-2">
                                £ <span id="price-min-display">50</span> - £ <span id="price-max-display">4,000</span>
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
                            @foreach($alltargets as $target)
                                @if(isset($targetFishCounts[$target->id]) && $targetFishCounts[$target->id] > 0)
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input filter-checkbox" 
                                               name="target_fish[]" 
                                               id="fish_{{ $target->id }}" 
                                               value="{{ $target->id }}"
                                               {{ in_array($target->id, request()->get('target_fish', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="fish_{{ $target->id }}">
                                            {{ app()->getLocale() == 'en' ? $target->name_en : $target->name }}
                                            <span class="count">({{ $targetFishCounts[$target->id] }})</span>
                                        </label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <hr>

                {{-- Methods --}}
                <div class="filter-section mb-3">
                    <div class="form-group mb-3">
                        <h5 class="mb-2">{{translate('Methods')}}</h5>
                        <div class="checkbox-group">
                            @foreach($guiding_methods as $method)
                                @if(isset($methodCounts[$method->id]) && $methodCounts[$method->id] > 0)
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input filter-checkbox" 
                                               name="methods[]" 
                                               id="method_{{ $method->id }}" 
                                               value="{{ $method->id }}"
                                               {{ in_array($method->id, request()->get('methods', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_{{ $method->id }}">
                                            {{ app()->getLocale() == 'en' ? $method->name_en : $method->name }}
                                            <span class="count">({{ $methodCounts[$method->id] }})</span>
                                        </label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <hr>

                {{-- Water Types --}}
                <div class="filter-section mb-3">
                    <div class="form-group mb-3">
                        <h5 class="mb-2">{{translate('Water Types')}}</h5>
                        <div class="checkbox-group">
                            @foreach($guiding_waters as $water)
                                @if(isset($waterTypeCounts[$water->id]) && $waterTypeCounts[$water->id] > 0)
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input filter-checkbox" 
                                               name="water[]" 
                                               id="water_{{ $water->id }}" 
                                               value="{{ $water->id }}"
                                               {{ in_array($water->id, request()->get('water', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="water_{{ $water->id }}">
                                            {{ app()->getLocale() == 'en' ? $water->name_en : $water->name }}
                                            <span class="count">({{ $waterTypeCounts[$water->id] }})</span>
                                        </label>
                                    </div>
                                @endif
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
    </style>
    
@endpush

@push('guidingListingScripts')
<script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterContainer');
        // Initialize main price slider
        const priceSliderMain = document.getElementById('price-slider-main');
        if (priceSliderMain) {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const initialMin = urlParams.get('price_min') || 50;
            const initialMax = urlParams.get('price_max') || 4000;

            noUiSlider.create(priceSliderMain, {
                start: [parseInt(initialMin), parseInt(initialMax)],
                connect: true,
                step: 50,
                range: {
                    'min': 50,
                    'max': 4000
                },
                format: {
                    to: function(value) {
                        return Math.round(value);
                    },
                    from: function(value) {
                        return Number(value);
                    }
                }
            });
    
            const priceMinDisplay = document.getElementById('price-min-display');
            const priceMaxDisplay = document.getElementById('price-max-display');
            const hiddenMinMain = document.getElementById('price_min_main');
            const hiddenMaxMain = document.getElementById('price_max_main');
    
            // Update display values during sliding
            priceSliderMain.noUiSlider.on('update', function(values, handle) {
                const value = values[handle];
    
                if (handle === 0) {
                    priceMinDisplay.textContent = numberWithCommas(value);
                    hiddenMinMain.value = value;
                } else {
                    priceMaxDisplay.textContent = numberWithCommas(value);
                    hiddenMaxMain.value = value;
                }
            });

            // Only trigger filter update when mouse is released
            priceSliderMain.noUiSlider.on('end', updateResults);
        }

        // Add change event listener to all filter checkboxes
        document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
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
                updateFilters(data);
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

        function updateFilters(data) {
            function updateCheckboxGroup(name, counts) {
                const checkboxes = document.querySelectorAll(`input[name="${name}[]"]`);
                if (!checkboxes.length) {
                    console.warn(`No checkboxes found for name: ${name}`);
                    return;
                }

                const checkboxGroup = checkboxes[0].closest('.checkbox-group');
                if (!checkboxGroup) {
                    console.warn(`No checkbox group found for name: ${name}`);
                    return;
                }

                const existingMsg = checkboxGroup.querySelector('.text-muted.small');
                if (existingMsg) {
                    existingMsg.remove();
                }

                let visibleCount = 0;
                checkboxes.forEach(checkbox => {
                    const checkboxContainer = checkbox.closest('.form-check');
                    console.log(checkboxContainer);
                    if (!checkboxContainer) return;

                    const id = checkbox.value;
                    const count = counts?.[id] || 0;
                    
                    // Keep checked items visible even if count is 0
                    if (count === 0 && !checkbox.checked) {
                        checkboxContainer.style.display = 'none';
                    } else {
                        checkboxContainer.style.display = '';
                        visibleCount++;
                        const countSpan = checkboxContainer.querySelector('.count');
                        if (countSpan) {
                            countSpan.textContent = `(${count})`;
                        }
                    }
                });

                // If no checkboxes are visible, show a message
                if (visibleCount === 0) {
                    const noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'text-muted small';
                    noResultsMsg.textContent = 'No options available';
                    checkboxGroup.appendChild(noResultsMsg);
                }
            }

            const filterCounts = data.filterCounts;
            // Update all filter groups
            if (filterCounts.targetFish) {
                updateCheckboxGroup('target_fish', filterCounts.targetFish);
            }
            if (filterCounts.waters) {
                updateCheckboxGroup('water', filterCounts.waters);
            }
            if (data.methodCounts) {
                updateCheckboxGroup('methods', data.methodCounts);
            }
        }
        
        // Make updateFilters available globally
        window.updateFilters = updateFilters;
    });
    
    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function reinitializeComponents() {
        document.querySelectorAll('.carousel').forEach(carousel => {
            new bootstrap.Carousel(carousel, {
                interval: false
            });
        });
    }
</script>
@endpush