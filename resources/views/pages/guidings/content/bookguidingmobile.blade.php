<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            @if($guiding->is_newguiding)
                <div class="card-body">
                <form action="{{ route('checkout') }}" method="POST">
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
                                <span id="priceLabel" data-from-text="{{ translate('From:') }}" data-price-text="{{ translate('Price:') }}">
                                    {{ translate('From:') }}
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
            @else
                <form action="{{ route('checkout') }}" class="tour-details-two__sidebar-form text-center" method="POST">
                    @csrf
                    <select class="form-select mb-2 mb-md-0" required id="person" name="person">
                        <option selected disabled value="">{{ translate('Bitte wähle die Personenzahl') }}</option>
                        @for ($i = 1; $i <= $guiding->max_guests; $i++)
                            @php
                                $price_property = 'price_'.str_replace(['2', '3', '4', '5'], ['two', 'three', 'four', 'five'], $i).'_persons';
                                $person = 'Person';
                                if($i > 1) $person = 'Personen';
                            @endphp
                            <option value="{{ $i }}">{{ $i }} {{ translate($person) }}</option>
                        @endfor
                    </select>
                    <hr class="d-none d-md-block">
                    <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                    <button type="submit" class="thm-btn col-12">{{ translate('Verfügbarkeit prüfen & buchen') }}</button>
                </form>
                <div class="price-details tour-details__top-left mt-md-3 pt-3 text-center">
                    <h2 class="tour-details__top-title d-md-block d-none">@lang('profile.price')</h2>
                    @for ($i = 1; $i <= $guiding->max_guests; $i++)
                        @if ($i == 1)
                            <p class="tour-details__top-rate" data-rate="1" style="display: flex;">
                                {{ translate('bei Buchung für 1 Person') }}
                                <span class="tour-details__top-price">{{$guiding->price}}€</span>
                            </p>
                        @else
                        @php
                            $price_property = 'price_'.str_replace(['2', '3', '4', '5'], ['two', 'three', 'four', 'five'], $i).'_persons';
                        @endphp
                            <p class="tour-details__top-rate" data-rate="{{ $i }}">
                                {{ translate('bei Buchung für')}} {{ $i }} {{ translate('Personen')}}
                                <span class="tour-details__top-price">{{ $guiding->{$price_property} }}€</span>
                            </p>
                        @endif
                    @endfor
                </div>
            @endif
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
        const price = selectedOption.getAttribute('data-price');

        if (price) {
            priceLabel.textContent = priceText;
            if (personCount === '1') {
                priceDisplay.textContent = `${price}€`;
            } else {
                priceDisplay.textContent = `${price}€ p.P.`;
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
