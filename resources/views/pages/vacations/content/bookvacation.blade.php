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
                                    @foreach($vacation->accommodations as $accommodationIndex => $accommodation)
                                        <option value="{{ $accommodation->id }}">{{ !empty($accommodation->title) ? $accommodation->title : translate('Accommodation ' . ($accommodationIndex + 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>{{ translate('Boat Rental') }}</label>
                                <select class="form-control" name="boat_id">
                                    <option value="">{{ translate('No boat needed') }}</option>
                                    @foreach($vacation->boats as $boatIndex => $boat)
                                        <option value="{{ $boat->id }}">{{ !empty($boat->title) ? $boat->title : translate('Boat ' . ($boatIndex + 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ translate('Guiding') }}</label>
                            <select class="form-control" name="guiding_id">
                                <option value="">{{ translate('No guiding needed') }}</option>
                                @foreach($vacation->guidings as $guidingIndex => $guiding)
                                    <option value="{{ $guiding->id }}">{{ !empty($guiding->title) ? $guiding->title : translate('Guiding ' . ($guidingIndex + 1)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ translate('Additional Services') }}</label>
                            <select class="form-control" name="additional_services">
                                <option value="">{{ translate('No additional services needed') }}</option>
                                {{-- @foreach($vacation->additional_services as $additionalServiceIndex => $additionalService)
                                    <option value="{{ $additionalService->id }}">{{ !empty($additionalService->title) ? $additionalService->title : translate('Additional Service ' . ($additionalServiceIndex + 1)) }}</option>
                                @endforeach --}}
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="hasPets" name="has_pets">
                                <label class="form-check-label" for="hasPets">{{ translate('Do you have pets?') }}</label>
                            </div>
                        </div>

                        <div class="total-price-container mb-4">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <h5 class="mb-0">{{ translate('Total Price') }}:</h5>
                                <h5 class="mb-0" id="total-price">€0.00</h5>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-orange w-100">{{ translate('Proceed to booking') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const form = document.querySelector('form');
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
        guidings: @json($vacation->guidings)
    };

    console.log('Vacation Data:', vacationData);

    // Add this debug log to check a sample dynamic_fields structure
    if (vacationData.accommodations && vacationData.accommodations.length > 0) {
        console.log('Sample Accommodation Dynamic Fields:', {
            raw: vacationData.accommodations[0].dynamic_fields,
            parsed: typeof vacationData.accommodations[0].dynamic_fields === 'string' 
                ? JSON.parse(vacationData.accommodations[0].dynamic_fields) 
                : vacationData.accommodations[0].dynamic_fields
        });
    }

    function calculatePrice(persons, item) {
        console.log('Calculating price for:', { 
            persons, 
            itemId: item?.id,
            dynamicFields: item?.dynamic_fields,
            rawItem: item 
        });
        
        if (!item || !item.dynamic_fields) {
            console.log('No item or dynamic_fields found');
            return 0;
        }
        
        let dynamicFields;
        try {
            dynamicFields = typeof item.dynamic_fields === 'string' 
                ? JSON.parse(item.dynamic_fields) 
                : item.dynamic_fields;
            console.log('Parsed Dynamic Fields:', dynamicFields);
        } catch (error) {
            console.error('Error parsing dynamic_fields:', error);
            return 0;
        }

        if (!dynamicFields.prices || !dynamicFields.prices.length) {
            console.log('No prices found in dynamic fields');
            return 0;
        }

        const capacity = parseInt(item.capacity) || parseInt(dynamicFields.bed_count) || dynamicFields.prices.length;
        const prices = dynamicFields.prices.map(price => parseFloat(price));
        const maxPrice = Math.max(...prices);

        console.log('Price Calculation Details:', {
            capacity,
            prices,
            maxPrice,
            persons
        });

        if (persons <= capacity) {
            // If persons is within capacity, use the corresponding price
            const price = persons > 0 && persons <= prices.length ? parseFloat(prices[persons - 1]) : 0;
            console.log('Within capacity price:', price);
            return price;
        } else {
            // Calculate price for exceeding capacity
            const fullGroups = Math.floor(persons / capacity);
            const remainder = persons % capacity;
            
            let totalPrice = fullGroups * maxPrice;
            if (remainder > 0) {
                totalPrice += remainder <= prices.length ? parseFloat(prices[remainder - 1]) : maxPrice;
            }
            
            console.log('Exceeding capacity price:', totalPrice);
            return totalPrice;
        }
    }

    function updateTotalPrice() {
        console.log('Updating total price...');
        
        // Add null checks for all inputs
        const persons = personInput && personInput.value ? parseInt(personInput.value) : 0;
        const bookingType = bookingTypeInput && bookingTypeInput.value ? bookingTypeInput.value : 'custom';
        
        console.log('Persons:', persons);
        console.log('Booking Type:', bookingType);
        
        let totalPrice = 0;

        if (bookingType === 'package' && packageSelect && packageSelect.value) {
            console.log('Calculating package price...');
            const selectedPackage = vacationData.packages.find(p => p.id === parseInt(packageSelect.value));
            console.log('Selected Package:', selectedPackage);
            totalPrice = calculatePrice(persons, selectedPackage);
        } else {
            console.log('Calculating custom price...');
            
            // Calculate accommodation price
            if (accommodationSelect && accommodationSelect.value) {
                const selectedAccommodation = vacationData.accommodations.find(a => a.id === parseInt(accommodationSelect.value));
                console.log('Selected Accommodation:', selectedAccommodation);
                const accommodationPrice = calculatePrice(persons, selectedAccommodation);
                console.log('Accommodation Price:', accommodationPrice);
                totalPrice += accommodationPrice;
            }

            // Calculate boat price
            if (boatSelect && boatSelect.value) {
                const selectedBoat = vacationData.boats.find(b => b.id === parseInt(boatSelect.value));
                console.log('Selected Boat:', selectedBoat);
                const boatPrice = calculatePrice(persons, selectedBoat);
                console.log('Boat Price:', boatPrice);
                totalPrice += boatPrice;
            }

            // Calculate guiding price
            if (guidingSelect && guidingSelect.value) {
                const selectedGuiding = vacationData.guidings.find(g => g.id === parseInt(guidingSelect.value));
                console.log('Selected Guiding:', selectedGuiding);
                const guidingPrice = calculatePrice(persons, selectedGuiding);
                console.log('Guiding Price:', guidingPrice);
                totalPrice += guidingPrice;
            }
        }

        console.log('Final Total Price:', totalPrice);

        if (totalPriceElement) {
            totalPriceElement.textContent = `€${totalPrice.toFixed(2)}`;
        }
    }

    // Add event listeners only if elements exist
    const formElements = [personInput, packageSelect, accommodationSelect, boatSelect, guidingSelect];
    formElements.forEach(element => {
        if (element) {
            element.addEventListener('change', updateTotalPrice);
        }
    });

    // Add event listeners for booking type buttons
    document.querySelectorAll('.booking-type-btn').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            if (bookingTypeInput) {
                bookingTypeInput.value = type;
            }
            // Update UI
            document.querySelectorAll('.booking-type-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide appropriate options
            const packageOptions = document.getElementById('package-options');
            const customOptions = document.getElementById('custom-options');
            if (packageOptions && customOptions) {
                if (type === 'package') {
                    packageOptions.style.display = 'block';
                    customOptions.style.display = 'none';
                } else {
                    packageOptions.style.display = 'none';
                    customOptions.style.display = 'block';
                }
            }
            
            setTimeout(updateTotalPrice, 0);
        });
    });

    // Initial price calculation
    updateTotalPrice();
});
</script>
