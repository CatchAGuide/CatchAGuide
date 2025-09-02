<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <style>
        .booking-icon {
            font-size: 12px;
            opacity: 0.7;
            color: #6c757d;
        }
    </style>
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
                                    <option value="{{ $price->person }}" data-price="{{ $price->amount }}" @if($guiding->price_type == 'per_boat') data-total-price="{{ $guiding->price }}" @endif>
                                        {{ $price->person }} {{ $price->person == 1 ? __('booking.person') : __('booking.people') }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="clearSelect" class="btn btn-link text-danger" style="display: none;">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Icons and price side by side -->
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="booking-price">
                                <span id="priceLabel" class="from-text">{{ __('booking.from') }}</span>
                                @if($guiding->price_type == 'per_person')
                                    <span id="priceDisplay" class="text-orange total-price">€</span>
                                @else
                                    <span id="priceDisplay" class="text-orange total-price">{{ number_format($guiding->price, 2, '.', '') == number_format($guiding->price, 0, '.', '') . '.00' ? number_format($guiding->price, 0, '.', '') : number_format($guiding->price, 2, '.', '') }}€</span>
                                @endif
                                <span class="per-guiding-text" style="display: none;">{{ __('booking.per_guiding') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="booking-price-container">
                        <div id="priceCalculation" class="price-calculation mt-1 mb-1">
                            <div class="price-breakdown">
                                <div class="text-center">
                                    <div class="price-item small">
                                        <span class="base-price"></span> {{ __('booking.per_person_for_a_tour_of') }} <span class="person-count"></span> <span class="people-text"></span>
                                    </div>
                                    
                                    @if($guiding->min_guests)
                                        <small class="mt-1 small text-muted"> * {{str_replace('[Min Guest]', $guiding->min_guests, __('booking.min_guest'))}}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-orange w-100" id="reserveButtonMobile">{{ __('booking.reserve_now') }}</button>
                    <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                    <input type="hidden" name="selected_date" id="selectedDateInputMobile" value="">
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
        const totPrice = selectedOption.getAttribute('data-total-price');
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
                const pricePerPerson = formatPriceMobile(totPrice / persons);
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
        // Only proceed if we have options to work with
        if (personSelect.options.length > 1) {
            const firstOption = personSelect.options[1]; // Index 1 because index 0 is the disabled option
            const persons = firstOption.value;
            const firstPrice = firstOption.getAttribute('data-price');
            const totPrice = firstOption.getAttribute('data-total-price');
            const personText = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
            
            if (firstPrice) {
                // Show price calculation with default values
                priceCalculation.style.display = 'block';
                
                @if($guiding->price_type == 'per_person')
                    // Update the total price display
                    totalPrice.textContent = formatPriceMobile(firstPrice) + '€';
                    const perPersonPrice = Math.round(firstPrice / persons);
                    basePrice.textContent = formatPriceMobile(perPersonPrice) + '€';
                @else
                    const maxGuest = firstOption.getAttribute('data-max-guest');
                    const perPersonPrice = Math.round(totPrice / persons );
                    basePrice.textContent = formatPriceMobile(perPersonPrice) + '€';
                @endif
                
                // Update person count and text
                personCount.textContent = persons;
                peopleText.textContent = personText;
            }
        }
    }
});

// Listen for calendar date selection events (outside DOMContentLoaded for immediate availability)
window.addEventListener('dateSelected', function(event) {
    console.log('Mobile booking: Date selected event received', event.detail); // Debug log
    const selectedDate = event.detail.date;
    const reserveButton = document.getElementById('reserveButtonMobile');
    const selectedDateInput = document.getElementById('selectedDateInputMobile');
    
    if (selectedDate && reserveButton) {
        // Format the date for display
        const date = new Date(selectedDate);
        const formattedDate = date.toLocaleDateString('{{ app()->getLocale() }}', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Update button text and hidden input
        reserveButton.textContent = `{{ __('booking.reserve_for_date') }} ${formattedDate}`;
        selectedDateInput.value = selectedDate;
        console.log('Mobile booking: Button text updated to', reserveButton.textContent); // Debug log
    }
});

// Listen for calendar date deselection events
window.addEventListener('dateDeselected', function(event) {
    console.log('Mobile booking: Date deselected event received'); // Debug log
    const reserveButton = document.getElementById('reserveButtonMobile');
    const selectedDateInput = document.getElementById('selectedDateInputMobile');
    
    if (reserveButton) {
        // Reset button text and clear hidden input
        reserveButton.textContent = '{{ __('booking.reserve_now') }}';
        selectedDateInput.value = '';
        console.log('Mobile booking: Button text reset'); // Debug log
    }
});

// Add form validation to prevent submission without person selection
document.querySelector('.checkout-form').addEventListener('submit', function(event) {
    const personSelect = document.getElementById('personSelect');
    
    if (!personSelect.value || personSelect.selectedIndex === 0) {
        event.preventDefault();
        
        // Show validation bubble
        const selectContainer = personSelect.closest('.booking-select');
        
        // Remove any existing validation bubble
        const existingBubble = document.querySelector('.validation-bubble');
        if (existingBubble) {
            existingBubble.remove();
        }
        
        // Create validation bubble
        const validationBubble = document.createElement('div');
        validationBubble.className = 'validation-bubble';
        validationBubble.innerHTML = `
            <div class="validation-bubble-content">
                {{ __('booking.please_select_number_of_people') }}
                <div class="validation-bubble-arrow"></div>
            </div>
        `;
        
        // Add bubble styles (mobile-optimized)
        validationBubble.style.cssText = `
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #dc3545;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
            text-align: center;
            margin: 0 10px;
            animation: fadeInBounce 0.3s ease-out;
        `;
        
        // Add CSS animation keyframes
        if (!document.querySelector('#bubble-animation-styles')) {
            const style = document.createElement('style');
            style.id = 'bubble-animation-styles';
            style.textContent = `
                @keyframes fadeInBounce {
                    0% {
                        opacity: 0;
                        transform: translateY(-10px) scale(0.9);
                    }
                    100% {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Add arrow styles
        const arrow = validationBubble.querySelector('.validation-bubble-arrow');
        arrow.style.cssText = `
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #dc3545;
        `;
        
        // Position container relatively for absolute positioning
        selectContainer.style.position = 'relative';
        selectContainer.appendChild(validationBubble);
        
        // Add error styling to select
        personSelect.classList.add('is-invalid');
        
        // Auto-hide bubble after 3 seconds
        setTimeout(() => {
            if (validationBubble && validationBubble.parentNode) {
                validationBubble.remove();
            }
        }, 3000);
        
        // Remove error styling and bubble when user makes selection
        personSelect.addEventListener('change', function() {
            personSelect.classList.remove('is-invalid');
            const bubble = document.querySelector('.validation-bubble');
            if (bubble) {
                bubble.remove();
            }
        }, { once: true });
        
        return false;
    }
});
</script>
