
<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours card shadow-sm" id="booking-tour">
            <div class="card-body p-4">
                
                <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                    @csrf
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        @if($guiding->price_type == 'per_person')
                            <h4 class="mb-0"><small class="from-text">{{ __('booking.from') }}</small> <span class="total-price">€</span> <span class="fs-6 fw-normal text-muted per-guiding-text" style="display: none;">{{ __('booking.per_guiding') }}</span></h4>
                        @else
                            <h4 class="mb-0"><span class="total-price">{{ $guiding->price }}€</span> <span class="fs-6 fw-normal text-muted per-guiding-text">{{ __('booking.per_guiding') }}</span></h4>
                        @endif
                        
                        <div class="booking-select" style="min-width: 150px;">
                            <select class="form-select border-0" aria-label="Personenanzahl" name="person" required id="personSelect">
                                <option selected disabled>{{ __('booking.people') }}</option>
                                @if($guiding->price_type == 'per_person')
                                    @foreach(json_decode($guiding->prices) as $price)
                                        <option value="{{ $price->person }}" data-price="{{ $price->amount }}">{{ $price->person }} {{ $price->person == 1 ? __('booking.person') : __('booking.people') }}</option>
                                    @endforeach
                                @else
                                    @for($i = 1; $i <= $guiding->max_guests; $i++)
                                        <option value="{{ $i }}" data-price="{{ $guiding->price }}">{{ $i }} {{ $i == 1 ? __('booking.person') : __('booking.people') }}</option>
                                    @endfor
                                @endif

                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <div id="priceCalculation" class="price-calculation mt-3" style="display: none;">
                        <div class="price-breakdown">
                            <div class="d-flex justify-content-between mb-2">
                                <div class="price-item">
                                    @if($guiding->price_type == 'per_person')
                                        <span class="base-price"></span> {{ __('booking.per_person_for_a_tour_of') }} <span class="person-count"></span> <span class="people-text"></span>{{ __('booking.you_wont_be_charged_yet')}}
                                    @else
                                        {{ __('booking.fixed_price_for') }} <span class="person-count"></span> <span class="people-text"></span>{{ __('booking.you_wont_be_charged_yet')}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($guiding->min_guests)
                        <small> * {{str_replace('[Min Guest]', $guiding->min_guests, __('booking.min_guest'))}} </small>
                    @endif
                    
                    <div class="booking-form-container">
                        <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                        <input type="hidden" name="selected_date" id="selectedDateInput" value="">
                        <button type="submit" class="btn btn-orange w-100 py-3 mb-3 reserve-now-btn" id="reserveButton">{{ __('booking.reserve_now') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @if(!$agent->ismobile())
    <div class="contact-card mb-4 mt-4 tour-details-two__book-tours">
        <h5 class="contact-card__title">{{ __('booking.contact_us') }}</h5>
        <div class="contact-card__content">
            <p class="">{{ __('booking.do_you_have_questions') }}</p>
            <div class="">
                <div class="contact-info">
                    <i class="fas fa-phone-alt me-2"></i>
                    <a href="tel:+49{{env('CONTACT_NUM')}}" class="text-decoration-none">+49 (0) {{env('CONTACT_NUM')}}</a>
                </div>
                <a href="#" class="btn btn-outline-orange" data-bs-toggle="modal" data-bs-target="#contactModal">
                    {{ __('booking.contact_form') }}
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const personSelect = document.getElementById('personSelect');
    const priceCalculation = document.getElementById('priceCalculation');
    const basePrice = document.querySelector('.base-price');
    const personCount = document.querySelector('.person-count');
    const peopleText = document.querySelector('.people-text');
    const totalPrice = document.querySelector('.total-price');
    const grandTotal = document.querySelector('.grand-total');
    const fromText = document.querySelector('.from-text');
    const perGuidingText = document.querySelector('.per-guiding-text');
    const priceItem = document.querySelector('.price-item');
    
    // Format price to remove .00 decimals
    function formatPrice(price) {
        return parseFloat(price).toFixed(2).replace(/\.00$/, '');
    }
    
    // Set default price display for per_person price type
    @if($guiding->price_type == 'per_person')
        // Get the first price option (lowest price)
        if (personSelect.options.length > 1) {
            const firstOption = personSelect.options[1]; // Index 1 because index 0 is the disabled "People" option
            const firstPrice = firstOption.getAttribute('data-price');
            
            // Update the total price display without changing the select
            if (firstPrice) {
                totalPrice.textContent = formatPrice(firstPrice) + '€';
                
                // Show price calculation with default values
                priceCalculation.style.display = 'block';
                const persons = firstOption.value;
                const perPersonPrice = Math.round(firstPrice / persons);
                basePrice.textContent = formatPrice(perPersonPrice) + '€';
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
            }
        }
    @else
        // For fixed price, show the calculation with default values for the first person option
        if (personSelect.options.length > 1) {
            const firstOption = personSelect.options[1]; // Index 1 because index 0 is the disabled "People" option
            const persons = firstOption.value;
            const price = firstOption.getAttribute('data-price');
            
            // Format the price to remove .00 decimals
            if (totalPrice) {
                totalPrice.textContent = formatPrice(price) + '€';
            }
            
            // Show price calculation with default values
            priceCalculation.style.display = 'block';
            personCount.textContent = persons;
            peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
            
            // Make sure we're showing the correct text for fixed price
            if (priceItem) {
                priceItem.innerHTML = '{{ __('booking.fixed_price_for') }} <span class="person-count">' + persons + '</span> <span class="people-text">' + (persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}') + '</span>{{ __('booking.you_wont_be_charged_yet')}}';
            }
        }
    @endif
    
    personSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const persons = selectedOption.value;
        const serviceFee = {{ $guiding->service_fee ?? 0 }};
        
        if (price && persons) {
            priceCalculation.style.display = 'block';
            
            // Hide the "From" text when a selection is made
            if (fromText) {
                fromText.style.display = 'none';
            }
            
            @if($guiding->price_type == 'per_person')
                const perPersonPrice = Math.round(price / persons);
                basePrice.textContent = formatPrice(perPersonPrice) + '€';
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                const subtotal = price;
                
                // Update the main price display
                totalPrice.textContent = formatPrice(subtotal) + '€';
                
                // Show the "per guiding" text after selection
                if (perGuidingText) {
                    perGuidingText.style.display = '';
                }
            @else
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                
                // Calculate price per person for display in the breakdown
                const pricePerPerson = formatPrice(price / persons);
                const subtotal = price;
                
                // Make sure the "per guiding" text remains visible
                if (perGuidingText) {
                    perGuidingText.style.display = '';
                }
                
                // Use the same format as per_person but with per-person calculation
                if (priceItem) {
                    // Directly construct the text without relying on translation strings that might be missing
                    const personText = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                    priceItem.innerHTML = pricePerPerson + '€ {{ __('booking.per_person_for_a_tour_of') }} <span class="person-count">' + persons + '</span> ' + personText + '. {{ __('booking.you_wont_be_charged_yet') }}';

                }
            @endif
            
            if (grandTotal) {
                grandTotal.textContent = formatPrice(parseInt(subtotal) + parseInt(serviceFee)) + '€';
            }
        } else {
            priceCalculation.style.display = 'none';
        }
    });
});

// Listen for calendar date selection events (outside DOMContentLoaded for immediate availability)
window.addEventListener('dateSelected', function(event) {
    console.log('Desktop booking: Date selected event received', event.detail); // Debug log
    const selectedDate = event.detail.date;
    const reserveButton = document.getElementById('reserveButton');
    const selectedDateInput = document.getElementById('selectedDateInput');
    
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
        console.log('Desktop booking: Button text updated to', reserveButton.textContent); // Debug log
    }
});

// Listen for calendar date deselection events
window.addEventListener('dateDeselected', function(event) {
    console.log('Desktop booking: Date deselected event received'); // Debug log
    const reserveButton = document.getElementById('reserveButton');
    const selectedDateInput = document.getElementById('selectedDateInput');
    
    if (reserveButton) {
        // Reset button text and clear hidden input
        reserveButton.textContent = '{{ __('booking.reserve_now') }}';
        selectedDateInput.value = '';
        console.log('Desktop booking: Button text reset'); // Debug log
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
        
        // Add bubble styles
        validationBubble.style.cssText = `
            position: absolute;
            top: -45px;
            right: 0;
            z-index: 1000;
            background: #dc3545;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        `;
        
        // Add arrow styles
        const arrow = validationBubble.querySelector('.validation-bubble-arrow');
        arrow.style.cssText = `
            position: absolute;
            bottom: -5px;
            right: 20px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #dc3545;
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
