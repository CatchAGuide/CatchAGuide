<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <h3 class="tour-details-two__sidebar-title d-none d-md-block">{{ translate('Book Vacation') }}</h3>
            <div class="card-body">
                <form action="{{ route('checkout') }}" method="POST">
                    @csrf
                    <div class="booking-form-container">
                        <div class="mb-3">
                            <label>{{ translate('Earliest availability') }}</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label>{{ translate('to') }}</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label>{{ translate('Duration') }}</label>
                            <input type="number" class="form-control" name="duration" required>
                        </div>
                        <div class="booking-select mb-3">
                            <label>{{ translate('Number of Person') }}</label>
                            <input type="number" class="form-control" name="person" required>
                        </div>
                        <input type="hidden" name="vacation_id" value="{{ $vacation->id }}">
                        <div class="mb-3">
                            @if(!empty($vacation->packages) && count($vacation->packages) > 0)
                                <div class="booking-type-buttons">
                                    <button type="button" class="booking-type-btn active" data-type="package">
                                        {{ translate('Package') }}
                                    </button>
                                    <button type="button" class="booking-type-btn" data-type="custom">
                                        {{ translate('Custom') }}
                                    </button>
                                </div>
                                <input type="hidden" name="booking_type" value="package">
                            @else
                                <input type="hidden" name="booking_type" value="custom">
                            @endif
                        </div>

                        @if(!empty($vacation->packages) && count($vacation->packages) > 0)
                            <div id="package-options" class="booking-options mb-3">
                                <div class="form-group">
                                    <label>{{ translate('Select Package') }}</label>
                                    <select class="form-control" name="package_id">
                                        <option value="" selected>{{ translate('No package needed') }}</option>
                                        @foreach($vacation->packages as $packageIndex => $package)
                                            <option value="{{ $package->id }}">{{ !empty($package->title) ? $package->title : translate('Package ' . ($packageIndex + 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="custom-options" class="booking-options mb-3" style="display: none;">
                        @else
                            <div id="custom-options" class="booking-options mb-3">
                        @endif
                            <div class="form-group mb-3">
                                <label>{{ translate('Accommodation') }}</label>
                                <select class="form-control" name="accommodation_id">
                                    <option value="" selected>{{ translate('No accommodation needed') }}</option>
                                    @foreach($vacation->accommodations as $accommodationIndex => $accommodation)
                                        <option value="{{ $accommodation->id }}">{{ !empty($accommodation->title) ? $accommodation->title : translate('Accommodation ' . ($accommodationIndex + 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>{{ translate('Boat Rental') }}</label>
                                <select class="form-control" name="boat_id">
                                    <option value="" selected>{{ translate('No boat needed') }}</option>
                                    @foreach($vacation->boats as $boatIndex => $boat)
                                        <option value="{{ $boat->id }}">{{ !empty($boat->title) ? $boat->title : translate('Boat ' . ($boatIndex + 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ translate('Guiding') }}</label>
                            <select class="form-control" name="guiding_id">
                                <option value="" selected>{{ translate('No guiding needed') }}</option>
                                @foreach($vacation->guidings as $guidingIndex => $guiding)
                                    <option value="{{ $guiding->id }}">{{ !empty($guiding->title) ? $guiding->title : translate('Guiding ' . ($guidingIndex + 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="hasPets" name="has_pets">
                                <label class="form-check-label" for="hasPets">{{ translate('Do you have pets?') }}</label>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>{{ translate('Extra offers') }}</label>
                            <div class="extra-offers-container">
                                @foreach($vacation->extras as $extraIndex => $extra)
                                    <div class="extra-offer-item d-flex align-items-center mb-2">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input extra-offer-checkbox" 
                                                   id="extra_{{ $extra->id }}" 
                                                   name="extra_offers[]" 
                                                   value="{{ $extra->id }}"
                                                   data-price-type="{{ $extra->type }}">
                                            <label class="form-check-label" for="extra_{{ $extra->id }}">
                                                {{ !empty($extra->description) ? $extra->description : translate('Extra offer ' . ($extraIndex + 1)) }}
                                                (€{{ number_format($extra->price, 2) }})
                                            </label>
                                        </div>
                                        @if($extra->type === 'per_person')
                                            <div class="quantity-input ms-2" style="display: none;">
                                                <input type="number" 
                                                       class="form-control form-control-sm extra-quantity" 
                                                       name="extra_quantity[{{ $extra->id }}]" 
                                                       min="1" 
                                                       value="1" 
                                                       style="width: 80px;">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="total-price-container mb-4">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <h5 class="mb-0">{{ translate('Total Price') }}:</h5>
                                <h5 class="mb-0" id="total-price">€0.00</h5>
                            </div>
                        </div>

                        <!-- Add checkout modal trigger -->
                        <button type="button" class="btn btn-orange w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                            {{ translate('Proceed to booking') }}
                        </button>

                        <!-- Add checkout modal -->
                        <div class="modal fade" 
                             id="checkoutModal" 
                             tabindex="-1" 
                             aria-labelledby="checkoutModalLabel" 
                             aria-hidden="true"
                             data-bs-backdrop="static"
                             data-bs-keyboard="false">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title" id="checkoutModalLabel">{{ translate('Contact Information') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form id="checkoutForm" action="{{ route('checkout') }}" method="POST">
                                            @csrf
                                            <div id="bookingDataContainer"></div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Title') }}</label>
                                                        <select class="form-control" name="title" required>
                                                            <option value="Mr">{{ translate('Mr.') }}</option>
                                                            <option value="Mrs">{{ translate('Mrs.') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Name') }}</label>
                                                        <input type="text" class="form-control" name="name" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Surname') }}</label>
                                                        <input type="text" class="form-control" name="surname" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Street') }}</label>
                                                        <input type="text" class="form-control" name="street" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Post Code') }}</label>
                                                        <input type="text" class="form-control" name="post_code" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('City') }}</label>
                                                        <input type="text" class="form-control" name="city" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Country') }}</label>
                                                        <input type="text" class="form-control" name="country" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Phone + Country Code') }}</label>
                                                        <div class="input-group">
                                                            <select class="form-select" name="phone_country_code" style="width: 100px; min-width: 100px;" required>
                                                                <option data-code="+1" value="+1">+1</option>
                                                                <option data-code="+44" value="+44">+44</option>
                                                                <option data-code="+49" value="+49">+49</option>
                                                                <option data-code="+33" value="+33">+33</option>
                                                                <option data-code="+34" value="+34">+34</option>
                                                                <option data-code="+39" value="+39">+39</option>
                                                                <option data-code="+31" value="+31">+31</option>
                                                                <option data-code="+32" value="+32">+32</option>
                                                                <option data-code="+41" value="+41">+41</option>
                                                                <option data-code="+43" value="+43">+43</option>
                                                                <option data-code="+46" value="+46">+46</option>
                                                                <option data-code="+47" value="+47">+47</option>
                                                                <option data-code="+45" value="+45">+45</option>
                                                                <option data-code="+358" value="+358">+358</option>
                                                                <option data-code="+48" value="+48">+48</option>
                                                            </select>
                                                            <input type="tel" class="form-control" name="phone" placeholder="Phone number" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ translate('Email') }}</label>
                                                        <input type="email" class="form-control" name="email" required>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-4">
                                                        <label class="form-label">{{ translate('Comments') }}</label>
                                                        <textarea class="form-control" name="comments" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                                                <button type="submit" class="btn btn-orange">{{ translate('Complete Booking') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update selectors to be more specific
    const form = document.querySelector('form[action*="checkout"]');
    const personInput = form.querySelector('input[name="person"]');
    const bookingTypeInput = form.querySelector('input[name="booking_type"]');
    const packageSelect = form.querySelector('select[name="package_id"]');
    const accommodationSelect = form.querySelector('select[name="accommodation_id"]');
    const boatSelect = form.querySelector('select[name="boat_id"]');
    const guidingSelect = form.querySelector('select[name="guiding_id"]');
    const totalPriceElement = document.getElementById('total-price');

    // Get vacation data
    const vacationData = {
        packages: @json($vacation->packages),
        accommodations: @json($vacation->accommodations),
        boats: @json($vacation->boats),
        guidings: @json($vacation->guidings),
        extras: @json($vacation->extras)
    };

    function calculatePrice(persons, item) {
        if (!item || !persons) {
            return 0;
        }

        try {
            let dynamicFields;
            if (typeof item.dynamic_fields === 'string') {
                dynamicFields = JSON.parse(item.dynamic_fields);
            } else {
                dynamicFields = item.dynamic_fields;
            }

            if (!dynamicFields || !dynamicFields.prices || !Array.isArray(dynamicFields.prices)) {
                return 0;
            }

            const prices = dynamicFields.prices.map(price => parseFloat(price) || 0);
            const capacity = parseInt(item.capacity) || parseInt(dynamicFields.bed_count) || prices.length;
            const maxPrice = Math.max(...prices);

            if (persons <= capacity) {
                const price = persons > 0 && persons <= prices.length ? prices[persons - 1] : 0;
                return price;
            } else {
                const fullGroups = Math.floor(persons / capacity);
                const remainder = persons % capacity;
                let totalPrice = fullGroups * maxPrice;
                
                if (remainder > 0) {
                    totalPrice += remainder <= prices.length ? prices[remainder - 1] : maxPrice;
                }
                
                return totalPrice;
            }
        } catch (error) {
            console.error('Error calculating price:', error);
            return 0;
        }
    }

    function updateTotalPrice() {        
        const persons = personInput && personInput.value ? parseInt(personInput.value) : 0;
        const bookingType = bookingTypeInput && bookingTypeInput.value ? bookingTypeInput.value : 'custom';
        
        let totalPrice = 0;
        console.group('Price Calculation');

        if (bookingType === 'package' && packageSelect && packageSelect.value) {
            const selectedPackage = vacationData.packages.find(p => p.id === parseInt(packageSelect.value));
            const packagePrice = calculatePrice(persons, selectedPackage);
            totalPrice += packagePrice;
        } else {
            // Calculate accommodation price
            if (accommodationSelect && accommodationSelect.value) {
                const selectedAccommodation = vacationData.accommodations.find(a => a.id === parseInt(accommodationSelect.value));
                const accommodationPrice = calculatePrice(persons, selectedAccommodation);
                totalPrice += accommodationPrice;
            }

            // Calculate boat price
            if (boatSelect && boatSelect.value) {
                const selectedBoat = vacationData.boats.find(b => b.id === parseInt(boatSelect.value));
                const boatPrice = calculatePrice(persons, selectedBoat);
                totalPrice += boatPrice;
            }

            // Calculate guiding price
            if (guidingSelect && guidingSelect.value) {
                const selectedGuiding = vacationData.guidings.find(g => g.id === parseInt(guidingSelect.value));
                const guidingPrice = calculatePrice(persons, selectedGuiding);
                totalPrice += guidingPrice;
            }
        }

        // Calculate extras price
        const extraCheckboxes = document.querySelectorAll('.extra-offer-checkbox:checked');
        let extrasTotal = 0;
        extraCheckboxes.forEach(checkbox => {
            const extraId = checkbox.value;
            const extra = vacationData.extras.find(e => e.id === parseInt(extraId));
            console.log(extra);
            console.log(extraId);
            console.log(extra.type);
            
            if (extra) {
                if (extra.type === 'per_person') {
                    const quantityInput = checkbox.closest('.extra-offer-item').querySelector('.extra-quantity');
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    const extraPrice = parseFloat(extra.price) * quantity;
                    extrasTotal += extraPrice;
                } else {
                    const extraPrice = parseFloat(extra.price);
                    extrasTotal += extraPrice;
                }
            }
        });
        
        totalPrice += extrasTotal;
        console.groupEnd();

        if (totalPriceElement) {
            totalPriceElement.textContent = `€${totalPrice.toFixed(2)}`;
        }
    }

    // Add event listeners for all form elements that affect price
    function addPriceUpdateListeners() {
        const elements = [
            personInput,
            packageSelect,
            accommodationSelect,
            boatSelect,
            guidingSelect
        ];

        elements.forEach(element => {
            if (element) {
                element.addEventListener('change', () => {
                    updateTotalPrice();
                });
            }
        });

        // Add listeners for extra offer checkboxes and quantity inputs
        document.querySelectorAll('.extra-offer-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                updateTotalPrice();
            });
        });

        document.querySelectorAll('.extra-quantity').forEach(input => {
            input.addEventListener('change', () => {
                updateTotalPrice();
            });
            input.addEventListener('input', () => {
                updateTotalPrice();
            });
        });
    }

    // Initialize price update listeners
    addPriceUpdateListeners();

    // Initial price calculation
    updateTotalPrice();

    // Initialize modal with specific options
    const modalElement = document.getElementById('checkoutModal');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false,
        focus: true
    });

    // Add event listener for modal show
    modalElement.addEventListener('shown.bs.modal', function () {
        // Ensure modal is interactive after showing
        this.style.pointerEvents = 'auto';
        this.querySelector('.modal-content').style.pointerEvents = 'auto';
        this.querySelector('.modal-body').style.pointerEvents = 'auto';
    });

    // Update modal trigger
    document.querySelector('[data-bs-target="#checkoutModal"]').addEventListener('click', function(e) {
        e.preventDefault();
        modal.show();
    });

    // Trigger initial calculations
    updateTotalPrice();

    // Add this new code block after the existing event listeners
    document.querySelectorAll('.extra-offer-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.extra-offer-item').querySelector('.quantity-input');
            if (this.dataset.priceType === 'per_person') {
                if (this.checked) {
                    quantityInput.style.display = 'block';
                } else {
                    quantityInput.style.display = 'none';
                }
            }
            updateTotalPrice();
        });
    });

    const phoneSelect = document.querySelector('select[name="phone_country_code"]');
    const countryNames = {
        '+1': 'USA/Canada',
        '+44': 'UK',
        '+49': 'Germany',
        '+33': 'France',
        '+34': 'Spain',
        '+39': 'Italy',
        '+31': 'Netherlands',
        '+32': 'Belgium',
        '+41': 'Switzerland',
        '+43': 'Austria',
        '+46': 'Sweden',
        '+47': 'Norway',
        '+45': 'Denmark',
        '+358': 'Finland',
        '+48': 'Poland'
    };

    // Add titles to options for tooltip
    phoneSelect.querySelectorAll('option').forEach(option => {
        const code = option.value;
        if (countryNames[code]) {
            option.title = countryNames[code];
        }
    });
});
</script>

<style>
    /* Reset and update z-index hierarchy */
    .modal {
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050 !important;
    }
    
    .modal-backdrop {
        display: none !important;
    }
    
    .modal-dialog {
        z-index: 10000 !important;
        position: relative;
    }
    
    .modal-content {
        position: relative;
        z-index: 10001 !important;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .modal-body {
        position: relative;
        z-index: 10002 !important;
    }
    
    /* Ensure form elements are clickable */
    .modal-body input,
    .modal-body select,
    .modal-body textarea,
    .modal-body button {
        position: relative;
        z-index: 10003 !important;
    }
    
    /* Additional modal styling */
    .modal-lg {
        max-width: 800px;
    }
    
    .modal-header {
        background-color: #f8f9fa;
        padding: 1rem 1.5rem;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
    }
    
    .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    
    .form-control:focus {
        border-color: #fd5d14;
        box-shadow: 0 0 0 0.2rem rgba(253, 93, 20, 0.25);
    }
    
    /* Add these styles to your existing style block */
    .input-group select.form-select {
        padding-right: 8px;
        padding-left: 8px;
        text-align: center;
    }
    
    /* Custom styling for the select to show only the code when selected */
    .input-group select.form-select option {
        text-align: left;
    }
    
    /* Ensure equal heights */
    .input-group > * {
        height: 38px;
        line-height: 1.5;
    }
    
    /* Add tooltip style for country names */
    .input-group select.form-select option[title]:hover::after {
        content: attr(title);
        position: absolute;
        background: #333;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1060;
    }
</style>
