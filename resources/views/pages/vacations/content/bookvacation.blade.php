<div class="col-md-12 {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="">
        <div class="tour-details-two__book-tours">
            <h3 class="tour-details-two__sidebar-title d-none d-md-block">{{ translate('Book Vacation') }}</h3>
            <div class="card-body">
                <div class="booking-form-container">
                    <form id="bookingForm">
                        <div class="mb-3">
                            <label>{{ translate('Earliest availability') }} <span class="required-field">*</span></label>
                            <input type="date" class="form-control required-input" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label>{{ translate('to') }} <span class="required-field">*</span></label>
                            <input type="date" class="form-control required-input" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label>{{ translate('Duration') }} <span class="required-field">*</span></label>
                            <select class="form-control required-input" name="duration_preset" id="duration_preset" required>
                                <option value="0">{{ translate('Select Duration') }}</option>
                                <option value="3">3 {{ translate('days') }}</option>
                                <option value="7">1 {{ translate('week') }}</option>
                                <option value="14">2 {{ translate('weeks') }}</option>
                                <option value="30">1 {{ translate('month') }}</option>
                                <option value="other">{{ translate('Other') }}</option>
                            </select>
                            <div id="custom_duration_container" style="display: none;" class="mt-2">
                                <input type="number" 
                                       class="form-control" 
                                       name="duration" 
                                       id="custom_duration" 
                                       placeholder="{{ translate('Enter number of days') }}"
                                       min="1">
                            </div>
                        </div>
                        <div class="booking-select mb-3">
                            <label>{{ translate('Number of Person') }} <span class="required-field">*</span></label>
                            <input type="number" class="form-control required-input" name="person" required>
                        </div>
                        <input type="hidden" name="vacation_id" value="{{ $vacation->id }}">
                        <div class="mb-3">
                            @if(!empty($vacation->packages) && count($vacation->packages) > 0)
                                <div class="booking-type-buttons">
                                    <button type="button" class="booking-type-btn active" data-type="package">
                                        {{ translate('Komplettpaket') }}
                                    </button>
                                    <button type="button" class="booking-type-btn" data-type="custom">
                                        {{ translate('Single Offer') }}
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

                            @if ($vacation->accommodations && count($vacation->accommodations) > 0)
                                <div class="form-group mb-3">
                                    <label>{{ translate('Accommodation') }}</label>
                                    <select class="form-control" name="accommodation_id">
                                        <option value="" selected>{{ translate('No accommodation needed') }}</option>
                                        @foreach($vacation->accommodations as $accommodationIndex => $accommodation)
                                            <option value="{{ $accommodation->id }}">{{ !empty($accommodation->title) ? $accommodation->title : translate('Accommodation ' . ($accommodationIndex + 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($vacation->boats && count($vacation->boats) > 0)
                                <div class="form-group mb-3">
                                    <label>{{ translate('Boat Rental') }}</label>
                                    <select class="form-control" name="boat_id">
                                        <option value="" selected>{{ translate('No boat needed') }}</option>
                                        @foreach($vacation->boats as $boatIndex => $boat)
                                            <option value="{{ $boat->id }}">{{ !empty($boat->title) ? $boat->title : translate('Boat ' . ($boatIndex + 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        @if ($vacation->guidings && count($vacation->guidings) > 0)
                            <div class="form-group mb-3">
                                <label>{{ translate('Guiding') }}</label>
                                <select class="form-control" name="guiding_id">
                                    <option value="" selected>{{ translate('No guiding needed') }}</option>
                                    @foreach($vacation->guidings as $guidingIndex => $guiding)
                                        <option value="{{ $guiding->id }}">{{ !empty($guiding->title) ? $guiding->title : translate('Guiding ' . ($guidingIndex + 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="hasPets" name="has_pets">
                                <label class="form-check-label" for="hasPets">{{ translate('Do you have pets?') }}</label>
                            </div>
                        </div>
                        
                        @if ($vacation->extras && count($vacation->extras) > 0)
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
                        @endif

                        <div class="total-price-container mb-4">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <h5 class="mb-0">{{ translate('Total Price') }}:</h5>
                                <h5 class="mb-0" id="total-price">€0.00</h5>
                            </div>
                        </div>

                        <!-- Add checkout modal trigger -->
                        <button type="button" 
                                class="btn btn-orange w-100" 
                                data-bs-target="#checkoutModal" 
                                id="proceedToBookingBtn" 
                                disabled
                                data-bs-placement="top" 
                                data-bs-title="{{ translate('Please complete all required fields marked with *') }}">
                            {{ translate('Proceed to booking') }}
                        </button>
                    </form>
                </div>

                <!-- Add checkout modal -->
                <div class="modal fade" 
                        id="checkoutModal" 
                        tabindex="-1" 
                        aria-labelledby="checkoutModalLabel" 
                        aria-hidden="true"
                        data-bs-backdrop="static"
                        data-bs-keyboard="false"
                        data-bs-focus="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title" id="checkoutModalLabel">{{ translate('Contact Information') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <!-- Add Booking Summary Section -->
                                <div class="booking-summary mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">{{ translate('Booking Summary') }}</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">{{ translate('Check-in') }}:</span>
                                                <span class="fw-medium" id="summary_start_date"></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">{{ translate('Check-out') }}:</span>
                                                <span class="fw-medium" id="summary_end_date"></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">{{ translate('Duration') }}:</span>
                                                <span class="fw-medium"><span id="summary_duration"></span> {{ translate('days') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">{{ translate('Guests') }}:</span>
                                                <span class="fw-medium"><span id="summary_persons"></span> {{ translate('person(s)') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">{{ translate('Booking Type') }}:</span>
                                                <span class="fw-medium" id="summary_booking_type"></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">{{ translate('Total Price') }}:</span>
                                                <span class="fw-medium" id="summary_total_price"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selected Items Summary -->
                                    <div class="selected-items mt-3" id="selected_items_summary"></div>
                                </div>

                                <form id="checkoutForm" action="{{ route('vacation.booking.store') }}" method="POST">
                                    @csrf
                                    <!-- Hidden inputs to carry over the booking data -->
                                    <input type="hidden" name="vacation_id" value="{{ $vacation->id }}">
                                    <input type="hidden" name="start_date" id="modal_start_date">
                                    <input type="hidden" name="end_date" id="modal_end_date">
                                    <input type="hidden" name="duration" id="modal_duration">
                                    <input type="hidden" name="person" id="modal_person">
                                    <input type="hidden" name="booking_type" id="modal_booking_type">
                                    <input type="hidden" name="package_id" id="modal_package_id">
                                    <input type="hidden" name="accommodation_id" id="modal_accommodation_id">
                                    <input type="hidden" name="boat_id" id="modal_boat_id">
                                    <input type="hidden" name="guiding_id" id="modal_guiding_id">
                                    <input type="hidden" name="has_pets" id="modal_has_pets">
                                    <div id="modal_extra_offers"></div>

                                    <!-- Existing contact form fields remain the same -->
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
                                                    <select class="form-select" name="phone_country_code" style=" max-width: 80px;" required>
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
                                    
                                    <div class="text-end d-flex">
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                                        <button type="submit" class="btn btn-orange">{{ translate('Complete Booking') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add thank you modal -->
<div class="modal fade" 
     id="thankYouModal" 
     tabindex="-1" 
     aria-labelledby="thankYouModalLabel" 
     aria-hidden="true"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-4 py-5">
                <i class="fas fa-check-circle text-success mb-4" style="font-size: 4rem;"></i>
                <h3 class="mb-4">{{ translate('Thank You for Your Booking Request!') }}</h3>
                <p class="mb-4">{{ translate('We have received your booking request and will process it shortly. You will receive a confirmation email with further details.') }}</p>
                <p class="text-muted mb-4">{{ translate('Booking Reference:') }} <span id="bookingReference"></span></p>
                <button type="button" class="btn btn-orange" data-bs-dismiss="modal">
                    {{ translate('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Create translations object for JavaScript
    const translations = {
        package: '{{ translate("Package") }}',
        custom: '{{ translate("Custom") }}',
        selectedPackage: '{{ translate("Selected Package") }}',
        selectedAccommodation: '{{ translate("Selected Accommodation") }}',
        selectedBoat: '{{ translate("Selected Boat") }}',
        selectedGuiding: '{{ translate("Selected Guiding") }}',
        accommodation: '{{ translate("Accommodation") }}',
        boat: '{{ translate("Boat") }}',
        guiding: '{{ translate("Guiding") }}',
        selectedItems: '{{ translate("Selected Items") }}',
        selectedExtras: '{{ translate("Selected Extras") }}',
        extraOffer: '{{ translate("Extra offer") }}',
        days: '{{ translate("days") }}',
        persons: '{{ translate("person(s)") }}',
        basePrice: '{{ translate("Base price for") }}',
        fixed: '{{ translate("Fixed price") }}',
        total: '{{ translate("Total") }}'
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Update form selector to target the new form ID
        const form = document.getElementById('bookingForm');
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

        // Update modal form submission
        const checkoutModal = document.getElementById('checkoutModal');
        checkoutModal.addEventListener('show.bs.modal', function () {
            // Transfer data from booking form to modal form
            const modalForm = document.getElementById('checkoutForm');
            
            // Update hidden fields with current form values
            modalForm.querySelector('#modal_start_date').value = form.querySelector('input[name="start_date"]').value;
            modalForm.querySelector('#modal_end_date').value = form.querySelector('input[name="end_date"]').value;
            
            // Handle duration value based on preset or custom input
            const durationPreset = document.getElementById('duration_preset');
            const customDuration = document.getElementById('custom_duration');
            const finalDuration = durationPreset.value === 'other' ? customDuration.value : durationPreset.value;
            modalForm.querySelector('#modal_duration').value = finalDuration;
            
            modalForm.querySelector('#modal_person').value = form.querySelector('input[name="person"]').value;
            modalForm.querySelector('#modal_booking_type').value = form.querySelector('input[name="booking_type"]').value;
            
            // Handle package/custom selections
            const packageId = form.querySelector('select[name="package_id"]')?.value || '';
            const accommodationId = form.querySelector('select[name="accommodation_id"]')?.value || '';
            const boatId = form.querySelector('select[name="boat_id"]')?.value || '';
            const guidingId = form.querySelector('select[name="guiding_id"]')?.value || '';
            
            modalForm.querySelector('#modal_package_id').value = packageId;
            modalForm.querySelector('#modal_accommodation_id').value = accommodationId;
            modalForm.querySelector('#modal_boat_id').value = boatId;
            modalForm.querySelector('#modal_guiding_id').value = guidingId;
            
            // Handle pets checkbox
            modalForm.querySelector('#modal_has_pets').value = form.querySelector('input[name="has_pets"]').checked;
            
            // Handle extra offers
            const extraOffersContainer = modalForm.querySelector('#modal_extra_offers');
            extraOffersContainer.innerHTML = ''; // Clear previous
            
            form.querySelectorAll('.extra-offer-checkbox:checked').forEach(checkbox => {
                const extraId = checkbox.value;
                const quantity = checkbox.closest('.extra-offer-item').querySelector('.extra-quantity')?.value || 1;
                
                extraOffersContainer.innerHTML += `
                    <input type="hidden" name="extra_offers[]" value="${extraId}">
                    <input type="hidden" name="extra_quantity[${extraId}]" value="${quantity}">
                `;
            });

            // Update summary sections
            const startDate = form.querySelector('input[name="start_date"]').value;
            const endDate = form.querySelector('input[name="end_date"]').value;
            const duration = finalDuration;
            const persons = form.querySelector('input[name="person"]').value;
            const bookingType = form.querySelector('input[name="booking_type"]').value;
            const totalPrice = document.getElementById('total-price').textContent;

            // Format dates
            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { 
                    day: 'numeric', 
                    month: 'short', 
                    year: 'numeric' 
                });
            };

            // Update summary elements
            document.getElementById('summary_start_date').textContent = formatDate(startDate);
            document.getElementById('summary_end_date').textContent = formatDate(endDate);
            document.getElementById('summary_duration').textContent = duration;
            document.getElementById('summary_persons').textContent = persons;
            document.getElementById('summary_booking_type').textContent = bookingType.charAt(0).toUpperCase() + bookingType.slice(1);
            document.getElementById('summary_total_price').textContent = totalPrice;

            // Build selected items summary with correct price calculations
            const selectedItemsSummary = document.getElementById('selected_items_summary');
            selectedItemsSummary.innerHTML = '<h6 class="mt-3 mb-2 border-bottom pb-2">' + translations.selectedItems + '</h6>';

            let totalCalculation = 0;

            if (bookingType === 'package' && packageSelect.value) {
                const selectedPackage = vacationData.packages.find(p => p.id === parseInt(packageSelect.value));
                if (selectedPackage) {
                    const packagePrice = calculatePrice(persons, selectedPackage);
                    totalCalculation += packagePrice;
                    selectedItemsSummary.innerHTML += `
                        <div class="selected-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="selected-item-info">
                                    <i class="fas fa-box me-2"></i>
                                    <span class="text-muted">${translations.package}:</span>
                                    <span class="ms-2 fw-medium">${selectedPackage.title || translations.selectedPackage}</span>
                                </div>
                                <span class="price-tag">€${packagePrice.toFixed(2)}</span>
                            </div>
                            <div class="price-calculation mt-2">
                                <small class="text-muted">
                                    ${translations.basePrice} ${persons} ${translations.persons}
                                </small>
                            </div>
                        </div>`;
                }
            } else {
                // Show selected accommodation with price
                if (accommodationSelect.value) {
                    const selectedAccommodation = vacationData.accommodations.find(a => a.id === parseInt(accommodationSelect.value));
                    if (selectedAccommodation) {
                        const accommodationPrice = calculatePrice(persons, selectedAccommodation);
                        totalCalculation += accommodationPrice;
                        selectedItemsSummary.innerHTML += `
                            <div class="selected-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="selected-item-info">
                                        <i class="fas fa-home me-2"></i>
                                        <span class="text-muted">${translations.accommodation}:</span>
                                        <span class="ms-2 fw-medium">${selectedAccommodation.title || translations.selectedAccommodation}</span>
                                    </div>
                                    <span class="price-tag">€${accommodationPrice.toFixed(2)}</span>
                                </div>
                                <div class="price-calculation mt-2">
                                    <small class="text-muted">
                                        ${translations.basePrice} ${persons} ${translations.persons}
                                    </small>
                                </div>
                            </div>`;
                    }
                }

                // Show selected boat with price
                if (boatSelect.value) {
                    const selectedBoat = vacationData.boats.find(b => b.id === parseInt(boatSelect.value));
                    if (selectedBoat) {
                        const boatPrice = calculatePrice(persons, selectedBoat);
                        totalCalculation += boatPrice;
                        selectedItemsSummary.innerHTML += `
                            <div class="selected-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="selected-item-info">
                                        <i class="fas fa-ship me-2"></i>
                                        <span class="text-muted">${translations.boat}:</span>
                                        <span class="ms-2 fw-medium">${selectedBoat.title || translations.selectedBoat}</span>
                                    </div>
                                    <span class="price-tag">€${boatPrice.toFixed(2)}</span>
                                </div>
                                <div class="price-calculation mt-2">
                                    <small class="text-muted">
                                        ${translations.basePrice} ${persons} ${translations.persons}
                                    </small>
                                </div>
                            </div>`;
                    }
                }

                // Show selected guiding with price
                if (guidingSelect.value) {
                    const selectedGuiding = vacationData.guidings.find(g => g.id === parseInt(guidingSelect.value));
                    if (selectedGuiding) {
                        const guidingPrice = calculatePrice(persons, selectedGuiding);
                        totalCalculation += guidingPrice;
                        selectedItemsSummary.innerHTML += `
                            <div class="selected-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="selected-item-info">
                                        <i class="fas fa-user-tie me-2"></i>
                                        <span class="text-muted">${translations.guiding}:</span>
                                        <span class="ms-2 fw-medium">${selectedGuiding.title || translations.selectedGuiding}</span>
                                    </div>
                                    <span class="price-tag">€${guidingPrice.toFixed(2)}</span>
                                </div>
                                <div class="price-calculation mt-2">
                                    <small class="text-muted">
                                        ${translations.basePrice} ${persons} ${translations.persons}
                                    </small>
                                </div>
                            </div>`;
                    }
                }
            }

            // Show selected extras with calculations
            const selectedExtras = form.querySelectorAll('.extra-offer-checkbox:checked');
            if (selectedExtras.length > 0) {
                let extrasTotal = 0;
                const extrasHtml = Array.from(selectedExtras).map(checkbox => {
                    const extra = vacationData.extras.find(e => e.id === parseInt(checkbox.value));
                    const quantity = checkbox.closest('.extra-offer-item').querySelector('.extra-quantity')?.value || 1;
                    const price = parseFloat(extra.price);
                    const totalPrice = extra.type === 'per_person' ? price * quantity : price;
                    extrasTotal += totalPrice;
                    
                    let calculationText = '';
                    if (extra.type === 'per_person') {
                        calculationText = `€${price.toFixed(2)} × ${quantity} ${translations.persons}`;
                    } else {
                        calculationText = `€${price.toFixed(2)} (${translations.fixed})`;
                    }

                    return `
                        <li class="selected-item-extra">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>${extra.description || translations.extraOffer}</span>
                                <span class="price-tag">€${totalPrice.toFixed(2)}</span>
                            </div>
                            <div class="price-calculation mt-1">
                                <small class="text-muted">${calculationText}</small>
                            </div>
                        </li>`;
                }).join('');

                totalCalculation += extrasTotal;
                selectedItemsSummary.innerHTML += `
                    <div class="selected-item">
                        <div class="mb-2">
                            <i class="fas fa-plus-circle me-2"></i>
                            <span class="text-muted">${translations.selectedExtras}:</span>
                        </div>
                        <ul class="list-unstyled ms-4 mb-0">
                            ${extrasHtml}
                        </ul>
                    </div>`;
            }

            // Add total calculation at the bottom
            selectedItemsSummary.innerHTML += `
                <div class="selected-item total-calculation mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">${translations.total}:</span>
                        <span class="price-tag fw-bold">€${totalCalculation.toFixed(2)}</span>
                    </div>
                </div>`;
        });

        // Add new validation function
        function validateBookingForm() {
            const startDate = form.querySelector('input[name="start_date"]').value;
            const endDate = form.querySelector('input[name="end_date"]').value;
            const durationPresetValue = document.getElementById('duration_preset').value;
            const customDurationValue = document.getElementById('custom_duration').value;
            const persons = form.querySelector('input[name="person"]').value;
            const bookingType = form.querySelector('input[name="booking_type"]').value;
            
            // Basic validation for required fields
            if (!startDate || !endDate || !persons) {
                return false;
            }

            // Validate duration
            if (durationPresetValue === 'other' && !customDurationValue) {
                return false;
            }

            // Validate dates
            const start = new Date(startDate);
            const end = new Date(endDate);
            if (end <= start) {
                return false;
            }

            // Validate persons
            if (persons <= 0) {
                return false;
            }

            // Package booking validation
            if (bookingType === 'package') {
                const packageId = form.querySelector('select[name="package_id"]')?.value;
                if (!packageId) {
                    return false;
                }
            } else {
                // Custom booking validation - accommodation is required, boat is optional
                const accommodationId = form.querySelector('select[name="accommodation_id"]')?.value;
                if (!accommodationId) {
                    return false;
                }
            }

            return true;
        }

        // Function to update button state
        function updateProceedButton() {
            const proceedButton = document.getElementById('proceedToBookingBtn');
            proceedButton.disabled = !validateBookingForm();
            updateButtonTooltip();
        }

        // Add validation listeners to all relevant form fields
        const fieldsToValidate = [
            'input[name="start_date"]',
            'input[name="end_date"]',
            'input[name="duration"]',
            'input[name="person"]',
            'select[name="package_id"]',
            'select[name="accommodation_id"]'
        ];

        fieldsToValidate.forEach(selector => {
            const element = form.querySelector(selector);
            if (element) {
                element.addEventListener('change', updateProceedButton);
                element.addEventListener('input', updateProceedButton);
            }
        });

        // Add listener for booking type changes
        const bookingTypeButtons = document.querySelectorAll('.booking-type-btn');
        bookingTypeButtons.forEach(button => {
            button.addEventListener('click', updateProceedButton);
        });

        // Initial validation check
        updateProceedButton();

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Update tooltip initialization
        function initializeTooltip() {
            const button = document.getElementById('proceedToBookingBtn');
            return new bootstrap.Tooltip(button, {
                trigger: 'hover focus'
            });
        }

        // Update the tooltip update function
        function updateButtonTooltip() {
            const button = document.getElementById('proceedToBookingBtn');
            const tooltip = bootstrap.Tooltip.getInstance(button);
            
            if (button.disabled) {
                const missingFields = getMissingRequiredFields();
                const tooltipText = `Please complete the following required fields: ${missingFields.join(', ')}`;
                
                if (tooltip) {
                    tooltip.dispose();
                }
                
                button.setAttribute('data-bs-title', tooltipText);
                initializeTooltip();
            } else {
                if (tooltip) {
                    tooltip.dispose();
                }
            }
        }

        // Initialize tooltip on page load
        initializeTooltip();

        // Get list of missing required fields
        function getMissingRequiredFields() {
            const missingFields = [];
            const requiredInputs = form.querySelectorAll('.required-input');
            
            requiredInputs.forEach(input => {
                if (!input.value) {
                    const label = input.previousElementSibling.textContent.replace(' *', '').trim();
                    missingFields.push(label);
                }
            });

            // Check package/accommodation based on booking type
            const bookingType = form.querySelector('input[name="booking_type"]').value;
            if (bookingType === 'package') {
                const packageSelect = form.querySelector('select[name="package_id"]');
                if (!packageSelect.value) {
                    missingFields.push('Package Selection');
                }
            } else {
                const accommodationSelect = form.querySelector('select[name="accommodation_id"]');
                if (!accommodationSelect.value) {
                    missingFields.push('Accommodation');
                }
            }

            return missingFields;
        }

        // Add visual feedback when clicking disabled button
        document.getElementById('proceedToBookingBtn').addEventListener('click', function(e) {
            if (this.disabled) {
                e.preventDefault();
                const requiredInputs = form.querySelectorAll('.required-input:invalid');
                requiredInputs.forEach(input => {
                    input.classList.add('highlight-required');
                    setTimeout(() => {
                        input.classList.remove('highlight-required');
                    }, 1500);
                });
            }
        });

        // Add input event listeners for real-time validation
        form.querySelectorAll('.required-input').forEach(input => {
            input.addEventListener('input', updateProceedButton);
        });

        // Initial validation check
        updateProceedButton();

        // Duration dropdown handling
        const durationPreset = document.getElementById('duration_preset');
        const customDurationContainer = document.getElementById('custom_duration_container');
        const customDuration = document.getElementById('custom_duration');

        durationPreset.addEventListener('change', function() {
            if (this.value === 'other') {
                customDurationContainer.style.display = 'block';
                customDuration.required = true;
                // Remove the required attribute from the preset dropdown
                this.required = false;
            } else {
                customDurationContainer.style.display = 'none';
                customDuration.required = false;
                // Add the required attribute back to the preset dropdown
                this.required = true;
            }
            updateProceedButton();
        });

        // Add duration fields to validation listeners
        durationPreset.addEventListener('change', updateProceedButton);
        customDuration.addEventListener('input', updateProceedButton);

        // Add this inside the DOMContentLoaded event listener
        bookingTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update booking type
                const bookingType = this.dataset.type;
                bookingTypeInput.value = bookingType;
                
                // Toggle active class
                bookingTypeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide relevant options
                const packageOptions = document.getElementById('package-options');
                const customOptions = document.getElementById('custom-options');
                
                if (bookingType === 'package') {
                    packageOptions.style.display = 'block';
                    customOptions.style.display = 'none';
                    // Clear custom selections
                    if (accommodationSelect) accommodationSelect.value = '';
                    if (boatSelect) boatSelect.value = '';
                } else {
                    packageOptions.style.display = 'none';
                    customOptions.style.display = 'block';
                    if (packageSelect) packageSelect.value = '';
                }
                
                // Update total price
                updateTotalPrice();
            });
        });

        // Add these date-related handlers near the top of the DOMContentLoaded function
        const startDateInput = form.querySelector('input[name="start_date"]');
        const endDateInput = form.querySelector('input[name="end_date"]');

        // Set minimum date to today for start date
        const today = new Date();
        const todayFormatted = today.toISOString().split('T')[0];
        startDateInput.min = todayFormatted;

        // Handle start date changes
        startDateInput.addEventListener('change', function() {
            const selectedStartDate = new Date(this.value);
            
            // Set the minimum end date to the day after the selected start date
            const nextDay = new Date(selectedStartDate);
            nextDay.setDate(nextDay.getDate() + 1);
            const nextDayFormatted = nextDay.toISOString().split('T')[0];
            
            // Update end date input
            endDateInput.min = nextDayFormatted;
            
            // If end date is before start date, reset it to the next day
            if (endDateInput.value && new Date(endDateInput.value) <= selectedStartDate) {
                endDateInput.value = nextDayFormatted;
            }
            
            // If end date is empty, set it to next day
            if (!endDateInput.value) {
                endDateInput.value = nextDayFormatted;
            }

            updateProceedButton();
            updateTotalPrice();
        });

        // Handle end date changes
        endDateInput.addEventListener('change', function() {
            updateProceedButton();
            updateTotalPrice();
        });

        // Update the form submission handling
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Disable submit button and show loading state
            const submitBtn = document.getElementById('proceedToBookingBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ translate("Processing...") }}';
            
            // Submit form via AJAX
            fetch('{{ route("vacation.booking.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide checkout modal
                    const checkoutModal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                    checkoutModal.hide();
                    
                    // Set booking reference in thank you modal
                    document.getElementById('bookingReference').textContent = data.booking.id;
                    
                    // Show thank you modal
                    const thankYouModal = new bootstrap.Modal(document.getElementById('thankYouModal'));
                    thankYouModal.show();
                    
                    // Reset form
                    this.reset();
                    bookingForm.reset();
                } else {
                    // Handle error
                    alert(data.message || '{{ translate("An error occurred. Please try again.") }}');
                }
            })
            .catch(error => {
                error.text().then(errorText => {
                    try {
                        const errorData = JSON.parse(errorText);
                        console.log(errorData);
                        alert(errorData.message || '{{ translate("An error occurred. Please try again.") }}');
                    } catch (e) {
                        // If not valid JSON, show generic error
                        alert('{{ translate("An error occurred. Please try again.") }}');
                    }
                }).catch(() => {
                    alert('{{ translate("An error occurred. Please try again.") }}');
                });
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '{{ translate("Proceed to booking") }}';
            });
        });

        // Add event listener for thank you modal close
        const thankYouModal = document.getElementById('thankYouModal');
        thankYouModal.addEventListener('hidden.bs.modal', function () {
            window.location.href = '{{ route("vacations.index") }}';
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
        /* z-index: 10003 !important; */
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

    /* Required field indicator */
    .required-field {
        color: #dc3545;
        margin-left: 4px;
    }

    /* Highlight required inputs that are empty */
    .required-input:invalid {
        border-color: #ffc107;
    }

    .required-input:invalid:focus {
        border-color: #fd5d14;
        box-shadow: 0 0 0 0.2rem rgba(253, 93, 20, 0.25);
    }

    /* Style for disabled button with tooltip */
    #proceedToBookingBtn:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Custom tooltip style */
    .tooltip {
        font-size: 0.875rem;
    }

    .tooltip-inner {
        background-color: #495057;
        max-width: 300px;
        padding: 8px 12px;
    }

    .tooltip.bs-tooltip-top .tooltip-arrow::before {
        border-top-color: #495057;
    }

    /* Highlight unfilled required fields when button is clicked */
    .highlight-required {
        animation: pulse 1.5s;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    /* Add these styles to your existing style block */
    .booking-summary {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .selected-item {
        margin-top: 0.75rem;
        padding: 0.75rem;
        background-color: white;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .selected-item:not(:last-child) {
        margin-bottom: 0.5rem;
    }

    .selected-item i {
        width: 20px;
        color: #fd5d14;
    }

    .selected-item ul li {
        padding: 0.5rem 0;
        font-size: 0.9rem;
        color: #6c757d;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .fw-medium {
        font-weight: 500;
        color: #2c3e50;
    }

    /* Add these new styles */
    .price-tag {
        font-weight: 500;
        color: #fd5d14;
        font-size: 0.95rem;
    }

    .selected-item {
        margin-top: 0.75rem;
        padding: 0.75rem;
        background-color: white;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .selected-item:not(:last-child) {
        margin-bottom: 0.5rem;
    }

    .selected-item i {
        width: 20px;
        color: #fd5d14;
    }

    .selected-item ul li {
        padding: 0.5rem 0;
        font-size: 0.9rem;
        color: #6c757d;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .booking-summary {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .fw-medium {
        font-weight: 500;
        color: #2c3e50;
    }

    /* Add these new styles */
    .price-calculation {
        font-size: 0.85rem;
        color: #6c757d;
        padding-left: 28px;
    }

    .selected-item-extra {
        padding: 0.5rem 0;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .selected-item-extra:first-child {
        border-top: none;
    }

    .total-calculation {
        border-top: 2px solid #dee2e6;
        padding-top: 1rem;
        margin-top: 1rem;
    }

    .price-tag {
        font-weight: 500;
        color: #fd5d14;
        font-size: 0.95rem;
    }

    /* Add styles for thank you modal */
    #thankYouModal .modal-content {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    #thankYouModal .modal-header {
        padding: 1.5rem 1.5rem 0;
    }

    #thankYouModal .modal-body {
        padding: 2rem;
    }

    #thankYouModal .fa-check-circle {
        color: #28a745;
    }

    #thankYouModal .btn-orange:hover {
        opacity: 0.9;
    }

    #bookingReference {
        font-weight: bold;
        color: #fd5d14;
    }

    /* Add these styles to your existing style block */
    input[type="date"] {
        cursor: pointer;
        position: relative;
        background-color: #fff;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: auto;
        height: auto;
        color: transparent;
        background: transparent;
        cursor: pointer;
    }

    /* Optional: Add a visual indicator that the whole field is clickable */
    input[type="date"] {
        padding-right: 30px; /* Make space for the custom calendar icon */
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23495057' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 16px;
    }

    /* Hide the default calendar icon in Edge */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
    }

    /* Ensure the field remains styled consistently with other form controls */
    input[type="date"]:hover {
        border-color: #fd5d14;
    }

    input[type="date"]:focus {
        border-color: #fd5d14;
        box-shadow: 0 0 0 0.2rem rgba(253, 93, 20, 0.25);
        outline: 0;
    }
</style>
