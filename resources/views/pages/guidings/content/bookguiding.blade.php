<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <h3 class="tour-details-two__sidebar-title d-none d-md-block">@lang('message.bookaguide')</h3>
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
        </div>
    </div>
</div>


