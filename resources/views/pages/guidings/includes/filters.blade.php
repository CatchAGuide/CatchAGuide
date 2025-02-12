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
                <div class="col-12">
                    <div class="input-group my-1">
                        <div class="input-group-prepend border-0 border-bottom ">
                            <span class="d-flex align-items-center px-2 h-100">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <select id="num-guests" class="form-control form-select border-0 border-bottom rounded-0 custom-select" name="num_guests">
                            <option value="" disabled selected hidden>@lang('message.number-of-guests')</option>
                            <option value="">@lang('message.choose')...</option>
                            <option value="1" {{ request()->get('num_guests') == 1 ? 'selected' : '' }}>1</option>
                            <option value="2" {{ request()->get('num_guests') == 2 ? 'selected' : '' }}>2</option>
                            <option value="3" {{ request()->get('num_guests') == 3 ? 'selected' : '' }}>3</option>
                            <option value="4" {{ request()->get('num_guests') == 4 ? 'selected' : '' }}>4</option>
                            <option value="5" {{ request()->get('num_guests') == 5 ? 'selected' : '' }}>5</option>
                        </select>
                    </div>
                </div>
             
                <div class="col-12">
                    <div class="form-group my-1 d-flex align-items-center border-bottom">
                        <div class="px-2 select2-icon">
                            <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                        </div>
                        <select class="form-control form-select border-0 rounded-0" id="target_fish" name="target_fish[]" style="width:100%">
                        </select>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-group my-1 d-flex align-items-center border-bottom">
                        <div class="px-2 select2-icon">
                            <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                        </div>
                        <select class="form-control form-select border-0 rounded-0" id="water" name="water[]" style="width:100%">
                        </select>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group my-1 d-flex align-items-center border-bottom">
                        <div class="px-2 select2-icon">
                            <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                        </div>
                        <select class="form-control form-select border-0 rounded-0" id="methods" name="methods[]" style="width:100%">
                        </select>
                    </div>
                </div>

                <div class="col-12 mb-2">
                    <div class="form-group my-1">
                        <label class="mb-2">{{translate('Your budget')}}</label>
                        <div class="price-range-slider">
                            <div id="price-slider-main"></div>
                            <div class="price-display mt-2">
                                ₱ <span id="price-min-display">50</span> - ₱ <span id="price-max-display">2000+</span>
                            </div>
                            <input type="hidden" name="price_min" id="price_min_main">
                            <input type="hidden" name="price_max" id="price_max_main">
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-12 ps-md-0">
                    <button class="btn btn-sm theme-primary btn-theme-new w-100 h-100">@lang('message.Search')</button>    
                </div>
            </div>
        </form>
    </div>
</div>

@section('css_after')
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
    </style>
@endsection

@section('js_after')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.css">
    <script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize main price slider
        const priceSliderMain = document.getElementById('price-slider-main');
        if (priceSliderMain) {
            noUiSlider.create(priceSliderMain, {
                start: [50, 2000],
                connect: true,
                step: 50,
                range: {
                    'min': 50,
                    'max': 2000
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
                    const displayValue = value >= 2000 ? '2,000+' : numberWithCommas(value);
                    priceMaxDisplay.textContent = displayValue;
                    hiddenMaxMain.value = value;
                }
            });

            // Only trigger filter update when mouse is released
            priceSliderMain.noUiSlider.on('end', function(values) {
                const min = values[0];
                const max = values[1];
                
                // Update form and make AJAX request
                const formData = new FormData(document.getElementById('filterContainer'));
                formData.set('price_range', `${min}-${max}`);
                
                const queryString = new URLSearchParams(formData).toString();
                const currentPath = window.location.pathname;

                fetch(`${currentPath}?${queryString}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Update the listings container
                    const listingsContainer = document.querySelector('.tours-list__inner');
                    if (listingsContainer) {
                        listingsContainer.innerHTML = data.html;
                        
                        // Reinitialize any necessary JavaScript components
                        reinitializeComponents();
                    }
                    
                    // Update search message if it exists
                    if (data.searchMessage) {
                        const messageElement = document.querySelector('.alert.alert-info');
                        if (messageElement) {
                            messageElement.textContent = data.searchMessage;
                        } else if (data.searchMessage.trim() !== '') {
                            // Create new message element if it doesn't exist
                            const newMessage = document.createElement('div');
                            newMessage.className = 'alert alert-info mb-3';
                            newMessage.role = 'alert';
                            newMessage.textContent = data.searchMessage;
                            listingsContainer.parentElement.insertBefore(newMessage, listingsContainer);
                        }
                    }
                    
                    // Update URL without page reload
                    const newUrl = `${currentPath}?${queryString}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);
                })
                .catch(error => {
                    console.error('Error updating results:', error);
                });
            });

            // Set initial values if they exist in the URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('price_range')) {
                const [min, max] = urlParams.get('price_range').split('-');
                priceSliderMain.noUiSlider.set([min, max]);
            }
        }
    });
    
    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Function to reinitialize components after AJAX update
    function reinitializeComponents() {
        // Reinitialize carousels
        document.querySelectorAll('.carousel').forEach(carousel => {
            new bootstrap.Carousel(carousel, {
                interval: false
            });
        });

        // Reinitialize any other components that need it
        // For example, tooltips, popovers, etc.
    }
    </script>
@endsection