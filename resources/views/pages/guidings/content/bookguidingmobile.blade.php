<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <div class="card-body pt-2">
            <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                @csrf
                <div class="booking-form-container">
                    <div class="booking-select position-relative">
                        <div style="display: flex;">
                            <select class="form-select" id="personSelect" aria-label="Personenanzahl" name="person" required>
                                <option selected disabled>{{ __('booking.people') }}</option>
                                @foreach(json_decode($guiding->prices) as $price)
                                    <option value="{{ $price->person }}" data-price="{{ $price->amount }}">
                                        {{ $price->person }} {{ $price->person == 1 ? __('booking.person') : __('booking.people') }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="clearSelect" class="btn btn-link text-danger" style="display: none;">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="booking-price">
                            @if($guiding->price_type == 'per_person')
                                <span id="priceLabel" class="from-text">{{ __('booking.from') }}</span>
                                <span id="priceDisplay" class="text-orange total-price">€</span>
                                <span class="per-guiding-text" style="display: none;">{{ __('booking.per_guiding') }}</span>
                            @else
                                <span id="priceDisplay" class="text-orange total-price">{{ number_format($guiding->price, 2, '.', '') == number_format($guiding->price, 0, '.', '') . '.00' ? number_format($guiding->price, 0, '.', '') : number_format($guiding->price, 2, '.', '') }}€</span>
                            @endif
                        </div>
                    </div>
                    <div class="booking-price-container">
                        <div id="priceCalculation" class="price-calculation mt-1 mb-1">
                            <div class="price-breakdown">
                                <div class="text-center">
                                    <div class="price-item small">
                                        @if($guiding->price_type == 'per_person')
                                            <span class="base-price"></span> {{ __('booking.per_person_for_a_tour_of') }} <span class="person-count"></span> <span class="people-text"></span>
                                        @else
                                            {{ __('booking.fixed_price_for') }} <span class="person-count"></span> <span class="people-text"></span>{{ __('booking.you_wont_be_charged_yet')}}
                                        @endif
                                    </div>
                                    
                                    @if($guiding->min_guests)
                                        <small class="mt-1 small text-muted"> * {{str_replace('[Min Guest]', $guiding->min_guests, __('booking.min_guest'))}}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-orange w-100">{{ __('booking.reserve_now') }}</button>
                    <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<script>

  document.addEventListener('DOMContentLoaded', function () {
    const personSelect = document.getElementById('personSelect');
    const priceLabel = document.getElementById('priceLabel');
    const priceDisplay = document.getElementById('priceDisplay');
    const clearSelect = document.getElementById('clearSelect');
    const priceCalculation = document.getElementById('priceCalculation');
    const basePrice = document.querySelector('.base-price');
    const personCount = document.querySelector('.person-count');
    const peopleText = document.querySelector('.people-text');
    const totalPrice = document.querySelector('.total-price');
    const fromText = document.querySelector('.from-text');
    const perGuidingText = document.querySelector('.per-guiding-text');
    const priceItem = document.querySelector('.price-item');
    
    // Format price to remove .00 decimals
    function formatPriceMobile(price) {
        return parseFloat(price).toFixed(2).replace(/\.00$/, '');
    }

    // Initialize price calculation with default values
    initializePriceCalculation();

    // Update price and label when a new option is selected
    personSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const persons = selectedOption.value;
        
        if (price && persons) {
            priceCalculation.style.display = 'block';
            
            // Hide the "From" text when a selection is made
            if (fromText) {
                fromText.style.display = 'none';
            }
            
            clearSelect.style.display = "block"; // Show the clear button
            
            @if($guiding->price_type == 'per_person')
                const perPersonPrice = Math.round(price / persons);
                basePrice.textContent = formatPriceMobile(perPersonPrice) + '€';
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                const subtotal = price;
                
                // Update the main price display
                totalPrice.textContent = formatPriceMobile(subtotal) + '€';
                
                // Show the "per guiding" text after selection
                if (perGuidingText) {
                    perGuidingText.style.display = '';
                }
            @else
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                
                // Calculate price per person for display in the breakdown
                const pricePerPerson = formatPriceMobile(price / persons);
                const subtotal = price;
                
                // Make sure the "per guiding" text remains visible
                if (perGuidingText) {
                    perGuidingText.style.display = '';
                }
                
                // Use the same format as per_person but with per-person calculation
                if (priceItem) {
                    // Directly construct the text without relying on translation strings that might be missing
                    const personText = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                    priceItem.innerHTML = pricePerPerson + '€ {{ __('booking.per_person_for_a_tour_of') }} <span class="person-count">' + persons + '</span> ' + personText;
                }
            @endif
        } else {
            priceCalculation.style.display = 'none';
            
            // Check if price is null and return to original text
            if (!price) {
                @if($guiding->price_type == 'per_person')
                    if (fromText) {
                        fromText.style.display = '';
                    }
                    if (perGuidingText) {
                        perGuidingText.style.display = 'none';
                    }
                    // Reset to initial state
                    initializePriceCalculation();
                @else
                    // Reset to initial state for fixed price
                    initializePriceCalculation();
                @endif
            } else {
                priceItem.innerHTML = price + '€ {{ __('booking.per_person_for_a_tour_of') }} <span class="person-count">' + persons + '</span> ';
            }
        }
    });

    // Clear selection and reset to default
    clearSelect.addEventListener('click', function () {
        personSelect.selectedIndex = 0;
        
        @if($guiding->price_type == 'per_person')
            if (fromText) {
                fromText.style.display = '';
            }
            if (perGuidingText) {
                perGuidingText.style.display = 'none';
            }
        @endif
        
        // Instead of hiding, reset to initial state
        initializePriceCalculation();
        clearSelect.style.display = "none"; // Hide the clear button
    });
    
    // Function to initialize price calculation with default values
    function initializePriceCalculation() {
        @if($guiding->price_type == 'per_person')
            // Get the first price option (lowest price)
            if (personSelect.options.length > 1) {
                const firstOption = personSelect.options[1]; // Index 1 because index 0 is the disabled option
                const firstPrice = firstOption.getAttribute('data-price');
                
                // Update the total price display without changing the select
                if (firstPrice) {
                    totalPrice.textContent = formatPriceMobile(firstPrice) + '€';
                    
                    // Show price calculation with default values
                    priceCalculation.style.display = 'block';
                    const persons = firstOption.value;
                    const perPersonPrice = Math.round(firstPrice / persons);
                    basePrice.textContent = formatPriceMobile(perPersonPrice) + '€';
                    personCount.textContent = persons;
                    peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                }
            }
        @else
            // For fixed price, show the calculation with default values for the first person option
            if (personSelect.options.length > 1) {
                const firstOption = personSelect.options[1]; // Index 1 because index 0 is the disabled option
                const persons = firstOption.value;
                const price = firstOption.getAttribute('data-price');
                
                // Show price calculation with default values
                priceCalculation.style.display = 'block';
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                
                // Make sure we're showing the correct text for fixed price
                if (priceItem) {
                    priceItem.innerHTML = '{{ __('booking.fixed_price_for') }} <span class="person-count">' + persons + '</span> <span class="people-text">' + (persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}') + '</span>';
                }
            }
        @endif
    }
});
</script>
