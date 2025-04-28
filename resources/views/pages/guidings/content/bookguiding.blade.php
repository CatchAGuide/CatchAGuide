
<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours card shadow-sm" id="booking-tour">
            <div class="card-body p-4">
                
                <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                    @csrf
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        @if($guiding->price_type == 'per_person')
                            <h4 class="mb-0"><small class="from-text">{{ __('booking.from') }}</small> <span class="total-price">0€</span></h4>
                        @else
                            <h4 class="mb-0">{{ $guiding->price }}€ <span class="fs-6 fw-normal text-muted">{{ __('booking.per_guiding') }}</span></h4>
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
                            <div class="d-flex justify-content-between mb-2 text-center">
                                <span class="price-item w-100 text-center">
                                    <span class="base-price"></span> {{ __('booking.per_person_for_a_tour_of') }} <span class="person-count"></span> <span class="people-text"></span>{{ __('booking.you_wont_be_charged_yet')}}
                                </span>
                                <span class="total-price fw-bold"></span>
                            </div>
                        </div>
                    </div>
                    
                    @if($guiding->min_guests)
                        <small> * {{str_replace('[Min Guest]', $guiding->min_guests, __('booking.min_guest'))}} </small>
                    @endif
                    
                    <div class="booking-form-container">
                        <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                        <button type="submit" class="btn btn-orange w-100 py-3 mb-3 reserve-now-btn">{{ __('booking.reserve_now') }}</button>
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
    
    // Set default price display for per_person price type
    @if($guiding->price_type == 'per_person')
        // Get the first price option (lowest price)
        if (personSelect.options.length > 1) {
            const firstOption = personSelect.options[1]; // Index 1 because index 0 is the disabled "People" option
            const firstPrice = firstOption.getAttribute('data-price');
            
            // Update the total price display without changing the select
            if (firstPrice) {
                totalPrice.textContent = firstPrice + '€';
                
                // Show price calculation with default values
                priceCalculation.style.display = 'block';
                const persons = firstOption.value;
                const perPersonPrice = Math.round(firstPrice / persons);
                basePrice.textContent = perPersonPrice + '€';
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
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
                basePrice.textContent = perPersonPrice + '€';
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                const subtotal = price;
            @else
                basePrice.textContent = price + '€';
                personCount.textContent = persons;
                peopleText.textContent = persons == 1 ? '{{ __('booking.person') }}' : '{{ __('booking.people') }}';
                const subtotal = price;
            @endif
            
            totalPrice.textContent = subtotal + '€';
            if (grandTotal) {
                grandTotal.textContent = (parseInt(subtotal) + parseInt(serviceFee)) + '€';
            }
        } else {
            priceCalculation.style.display = 'none';
        }
    });
});
</script>
