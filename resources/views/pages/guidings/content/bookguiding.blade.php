<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <h3 class="tour-details-two__sidebar-title d-none d-md-block">@lang('message.bookaguide')</h3>
            @if($guiding->is_newguiding)
                <div class="card-body">
                    <form action="{{ route('checkout') }}" method="POST">
                        @csrf
                        <div class="booking-form-container">
                            <div class="booking-select mb-md-3">
                                <select class="form-select" aria-label="Personenanzahl" name="person" required>
                                    <option selected disabled>{{ translate('Please select number of people') }}</option>
                                    @if($guiding->price_type == 'per_person')
                                        @foreach(json_decode($guiding->prices) as $price)
                                            <option value="{{ $price->person }}">{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</option>
                                        @endforeach
                                    @else
                                        <option selected value="1">1 Person {{ $guiding->prices }}</option>
                                        <option value="2">2 Personen</option>
                                        <option value="3">3 Personen</option>
                                        <option value="4">4 Personen</option>
                                    @endif
                                </select>
                            </div>
                            <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                            <button type="submit" class="btn btn-orange w-100">{{ translate('Book now') }}</button>

                        </div>
                    </form>
                    <div class="mt-3">
                        <h5>{{ translate('Price') }}</h5>
                        @if($guiding->price_type == 'per_person')
                            <ul class="list-unstyled">
                                @foreach(json_decode($guiding->prices) as $price)
                                    <li class="d-flex justify-content-between align-items-center">
                                        <span class="">{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</span>
                                        <span class="text-right">
                                            @if($price->person > 1)
                                                <span class="text-orange">{{ $price->person > 1 ? round($price->amount / $price->person) : $price->amount }}€</span>
                                                <span class="text-black" style="font-size: 0.8em;"> p.P</span>
                                                @else
                                                <span class="text-orange me-3 pe-1">{{ $price->person > 1 ? round($price->amount / $price->person) : $price->amount }}€</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <ul class="list-unstyled">
                                @foreach(json_decode($guiding->prices) as $price)
                                    <li class="d-flex justify-content-between align-items-center">
                                        <span>{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</span>
                                        <span class="text-right">
                                            <span class="text-danger">{{ round($price->amount / $price->person) }}€</span>
                                            @if($price->person > 1)
                                                <span class="text-black" style="font-size: 0.8em;"> p.P</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
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
