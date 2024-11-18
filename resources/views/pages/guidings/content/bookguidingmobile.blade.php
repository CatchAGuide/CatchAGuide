<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <!-- <h3 class="tour-details-two__sidebar-title d-none d-md-block">@lang('message.bookaguide')</h3> -->
            @if($guiding->is_newguiding)
                <div class="card-body">
                <form action="{{ route('checkout') }}" method="POST">
                    @csrf
                    <div class="booking-form-container">
                        <div class="booking-select">
                        <div class="booking-price">
                                <span>{{ translate('Price:') }}</span>
                                <span id="priceDisplay" class="text-orange">{{ $guiding->getLowestPrice() }}€ p.P.</span>
                            </div>
                            <select class="form-select" id="personSelect" aria-label="Personenanzahl" name="person" required>
                                <option selected disabled>Bitte wählen</option>
                                @if($guiding->price_type == 'per_person')
                                    @foreach(json_decode($guiding->prices) as $price)
                                        <option value="{{ $price->person }}" data-price="{{ round($price->amount / $price->person) }}">
                                            {{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}
                                        </option>
                                    @endforeach
                                @else
                                    @foreach(json_decode($guiding->prices) as $price)
                                        <option value="{{ $price->person }}" data-price="{{ $price->amount }}">
                                            {{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                           
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
        const priceDisplay = document.getElementById('priceDisplay');

        // Update price when a new option is selected
        personSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const personCount = selectedOption.value; // Get the value of the selected option (number of people)
            const price = selectedOption.getAttribute('data-price');

            if (price) {
                // Check if the selected option is for 1 person
                if (personCount === '1') {
                    priceDisplay.textContent = `${price}€`; // No "p.P." for 1 person
                } else {
                    priceDisplay.textContent = `${price}€ p.P.`; // Add "p.P." for more than 1 person
                }
            }
        });
    });
</script>