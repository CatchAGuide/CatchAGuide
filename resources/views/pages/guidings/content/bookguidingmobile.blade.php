<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <div class="card-body">
            <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                @csrf
                <div class="booking-form-container">
                    <div class="booking-select position-relative">
                        <div style="display: flex;">
                            <select class="form-select" id="personSelect" aria-label="Personenanzahl" name="person" required>
                                <option selected disabled>Bitte wählen</option>
                                @foreach(json_decode($guiding->prices) as $price)
                                    <option value="{{ $price->person }}" data-price="{{ round($price->amount / $price->person) }}">
                                        {{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="clearSelect" class="btn btn-link text-danger" style="display: none;">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="booking-price">
                            <span id="priceLabel" data-from-text="@lang('message.From')" data-price-text="{{ translate('Price:') }}">
                            @lang('message.From')
                            </span>
                            <span id="priceDisplay" class="text-orange">{{ $guiding->getLowestPrice() }}€ p.P.</span>
                        </div>
                    </div>
                    <div class="booking-price-container">
                        <div id="priceCalculation" class="price-calculation mt-3 mb-3">
                            <div class="price-breakdown">
                                <div class="text-center mb-3">
                                    <div class="price-item small">
                                        <span class="base-price"></span> {{ strtolower(translate('per person for a tour of')) }} <span class="person-count"></span> {{ strtolower(translate('people')) }}.
                                    </div>
                                    <div class="mt-1 small text-muted">
                                        {{ translate('You wont be charged yet.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-orange w-100">{{ translate('Book now') }}</button>
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

    const fromText = priceLabel.getAttribute('data-from-text');
    const priceText = priceLabel.getAttribute('data-price-text');

    // Initialize price calculation with default values
    initializePriceCalculation();

    // Update price and label when a new option is selected
    personSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const personValue = selectedOption.value;
        @if($guiding->price_type == 'per_person')
            const price = selectedOption.getAttribute('data-price');
        @else
            const price = {{ $guiding->price }};
        @endif

        if (price) {
            priceLabel.textContent = priceText;
            if (personValue === '1') {
                priceDisplay.textContent = `${price}€`;
            } else {
                @if($guiding->price_type == 'per_person')
                    priceDisplay.textContent = `${price}€ p.P.`;
                @else
                    priceDisplay.textContent = `${Math.round(price / personValue)}€ p.P.`;
                @endif
            }
            clearSelect.style.display = "block"; // Show the clear button
            
            // Update price calculation with selected values
            @if($guiding->price_type == 'per_person')
                const perPersonPrice = Math.round(price / personValue);
                basePrice.textContent = perPersonPrice + '€';
            @else
                basePrice.textContent = Math.round(price / personValue) + '€';
            @endif
            personCount.textContent = personValue;
        }
    });

    // Clear selection and reset to default
    clearSelect.addEventListener('click', function () {
        personSelect.selectedIndex = 0;
        priceLabel.textContent = fromText;
        priceDisplay.textContent = "{{ $guiding->getLowestPrice() }}€ p.P.";
        clearSelect.style.display = "none"; // Hide the clear button
        initializePriceCalculation();
    });
    
    // Function to initialize price calculation with default values
    function initializePriceCalculation() {
        @if($guiding->price_type == 'per_person')
            // Get the first price option (lowest price)
            if (personSelect.options.length > 1) {
                const firstOption = personSelect.options[1]; // Index 1 because index 0 is the disabled option
                const firstPrice = firstOption.getAttribute('data-price');
                
                // Show price calculation with default values but don't change the select
                if (firstPrice) {
                    const persons = firstOption.value;
                    const perPersonPrice = Math.round(firstPrice / persons);
                    basePrice.textContent = perPersonPrice + '€';
                    personCount.textContent = persons;
                }
            }
        @else
            const price = {{ $guiding->price }};
            const defaultPersons = 1;
            basePrice.textContent = price + '€';
            personCount.textContent = defaultPersons;
        @endif
    }
});

</script>
