<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours card shadow-sm" id="booking-tour">
            <div class="card-body p-4">
                
                <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                    @csrf
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        @if($guiding->price_type == 'per_person')
                            <h4 class="mb-0 total-price">0€</h4>
                        @else
                            <h4 class="mb-0">{{ $guiding->price }}€ <span class="fs-6 fw-normal text-muted">{{ translate('per guiding') }}</span></h4>
                        @endif
                        
                        <div class="booking-select" style="min-width: 150px;">
                            <select class="form-select border-0" aria-label="Personenanzahl" name="person" required id="personSelect">
                                <option selected disabled>{{ translate('People') }}</option>
                                @if($guiding->price_type == 'per_person')
                                    @foreach(json_decode($guiding->prices) as $price)
                                        <option value="{{ $price->person }}" data-price="{{ $price->amount }}">{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</option>
                                    @endforeach
                                @else
                                    @for($i = 1; $i <= $guiding->max_guests; $i++)
                                        <option value="{{ $i }}" data-price="{{ $guiding->price }}">{{ $i }} {{ $i == 1 ? 'Person' : 'Personen' }}</option>
                                    @endfor
                                @endif
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <div id="priceCalculation" class="price-calculation mt-3" style="display: none;">
                        <div class="price-breakdown">
                            <div class="d-flex justify-content-between mb-2 text-center">
                                <span class="price-item w-100 text-center">
                                    <span class="base-price"></span>  {{ strtolower(translate('per person for a tour of')) }} <span class="person-count"></span> {{ strtolower(translate('people')) }}
                                </span>
                                <span class="total-price fw-bold"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-form-container">
                        <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                        <button type="submit" class="btn btn-orange w-100 py-3 mb-3">{{ translate('Reserve now') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @if(!$agent->ismobile())
    <div class="contact-card mb-4 mt-4 tour-details-two__book-tours">
        <h5 class="contact-card__title">{{ translate('Contact us') }}</h5>
        <div class="contact-card__content">
            <p class="">{{ translate('Do you have questions about this fishing tour? Our team is here to help!') }}</p>
            <div class="">
                <div class="contact-info">
                    <i class="fas fa-phone-alt me-2"></i>
                    <a href="tel:+49{{env('CONTACT_NUM')}}" class="text-decoration-none">+49 (0) {{env('CONTACT_NUM')}}</a>
                </div>
                <a href="{{ route('additional.contact') }}" class="btn btn-outline-orange">
                    {{ translate('Contact Form') }}
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
    const totalPrice = document.querySelector('.total-price');
    const grandTotal = document.querySelector('.grand-total');
    
    // Set default selection for per_person price type
    @if($guiding->price_type == 'per_person')
        // Select the first option (lowest number of people)
        if (personSelect.options.length > 1) {
            personSelect.selectedIndex = 1; // Index 1 because index 0 is the disabled "People" option
            
            // Manually calculate and display the price
            const selectedOption = personSelect.options[personSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const persons = selectedOption.value;
            const serviceFee = {{ $guiding->service_fee ?? 0 }};
            
            if (price && persons) {
                priceCalculation.style.display = 'block';
                
                const perPersonPrice = Math.round(price / persons);
                basePrice.textContent = perPersonPrice + '€';
                personCount.textContent = persons;
                const subtotal = price;
                
                totalPrice.textContent = subtotal + '€';
                if (grandTotal) {
                    grandTotal.textContent = (parseInt(subtotal) + parseInt(serviceFee)) + '€';
                }
                
                // Also update the main total price display if it exists
                const mainTotalPrice = document.querySelector('h4.total-price');
                if (mainTotalPrice) {
                    mainTotalPrice.textContent = subtotal + '€';
                }
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
            
            @if($guiding->price_type == 'per_person')
                const perPersonPrice = Math.round(price / persons);
                basePrice.textContent = perPersonPrice + '€';
                personCount.textContent = persons;
                const subtotal = price;
            @else
                basePrice.textContent = price + '€';
                personCount.textContent = persons;
                const subtotal = price;
            @endif
            
            totalPrice.textContent = subtotal + '€';
            if (grandTotal) {
                grandTotal.textContent = (parseInt(subtotal) + parseInt(serviceFee)) + '€';
            }
            
            // Update the main total price display if it exists
            const mainTotalPrice = document.querySelector('h4.total-price');
            if (mainTotalPrice) {
                mainTotalPrice.textContent = subtotal + '€';
            }
        } else {
            priceCalculation.style.display = 'none';
        }
    });
});
</script>
