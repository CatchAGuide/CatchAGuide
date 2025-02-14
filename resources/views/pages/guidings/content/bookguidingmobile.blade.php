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
                        <button type="submit" class="btn btn-orange w-100">{{ translate('Book now') }}</button>
                    </div>
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

    const fromText = priceLabel.getAttribute('data-from-text');
    const priceText = priceLabel.getAttribute('data-price-text');

    // Update price and label when a new option is selected
    personSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const personCount = selectedOption.value;
        @if($guiding->price_type == 'per_person')
            const price = selectedOption.getAttribute('data-price');
        @else
            const price = {{ $guiding->price }};
        @endif

        if (price) {
            priceLabel.textContent = priceText;
            if (personCount === '1') {
                priceDisplay.textContent = `${price}€`;
            } else {
                @if($guiding->price_type == 'per_person')
                    priceDisplay.textContent = `${price}€ p.P.`;
                @else
                    priceDisplay.textContent = `${Math.round(price / personCount)}€ p.P.`;
                @endif
            }
            clearSelect.style.display = "block"; // Show the clear button
        }
    });

    // Clear selection and reset to default
    clearSelect.addEventListener('click', function () {
        personSelect.selectedIndex = 0;
        priceLabel.textContent = fromText;
        priceDisplay.textContent = "{{ $guiding->getLowestPrice() }}€ p.P.";
        clearSelect.style.display = "none"; // Hide the clear button
    });
});

</script>
