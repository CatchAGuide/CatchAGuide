<div>
    <form wire:submit.prevent="save" method="POST">
        <div>

            @if ($page == 1)
                <input name="locale" type="hidden" wire:model.lazy="locale" value="{{app()->getLocale()}}">
                <div class="row text-center">

                    <div class="col-12">
                        <button type="button" class="btn btn-outline-theme my-1" wire:click="next('holiday')">Fishing holiday</button>
                        <button type="button" class="btn btn-outline-theme my-1" wire:click="next('tour')">Fishing tour</button>
                    </div>
                </div>
            @endif
            @if($fishingType == 'holiday')

                @if ($page == 2)
                        <div class="d-flex">
                            <div class="counter">
                                <span class="h2">1</span><span>/7</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-6">
                                <label class="text-dark fw-bold date-wrapper">@lang('search-request.country')<span style="color:red;">*</span></label>
                                <input type="text" class="form-control  @error('country') is-invalid @enderror"  wire:model.lazy="country">
                            </div>
                            <div class="col-12 col-md-12 col-lg-6">
                                <label class="text-dark fw-bold date-wrapper">@lang('search-request.region')<span style="color:red;">*</span></label>
                                <input type="text" class="form-control  @error('region') is-invalid @enderror"  wire:model.lazy="region">
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>
                @endif

                @if ($page == 3)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">2</span><span>/7</span>
                    </div>
                </div>

                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-6">
                            <label class="text-dark fw-bold date-wrapper">@lang('search-request.target_fish')<span style="color:red;">*</span></label>
                            <input type="text" class="form-control  @error('target_fish') is-invalid @enderror"  wire:model.lazy="target_fish">
                        </div>
                        <div class="col-12 col-md-12 col-lg-6">
                            <label class="text-dark fw-bold date-wrapper">@lang('search-request.num_of_guest')<span style="color:red;">*</span></label>
                            <input class="form-control @error('number_of_guest') is-invalid @enderror"  type="number" wire:model.lazy="number_of_guest">
                        </div>
                    </div>
                    <div class="my-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                        <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                    </div>
                @endif

                @if ($page == 4)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">3</span><span>/7</span>
                    </div>
                </div>

                        <div class="row" >
                            @if($calendar)
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 date-wrapper">
                                    <div>
                                        <p class="text-dark fw-bold date-wrapper">@lang('search-request.time_frame')<span style="color:red;">*</span></p>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="me-2">
                                            From: @if($date_from)<span class="color-primary">{{ \Carbon\Carbon::parse($date_from)->format('F j, Y') }}</span>@else - @endif
                                        </div>
                                        <div>
                                            To: @if($date_to)<span class="color-primary">{{ \Carbon\Carbon::parse($date_to)->format('F j, Y') }}</span>@else - @endif
                                        </div>

                                    </div>

                                    <div id="date-from" wire:ignore>

                                    </div>

                                </div>
                            @endif
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 my-3">
                                <input type="checkbox" id="letmeknow" {{$letmeknow ? 'checked' : ''}} wire:click="letMeKnow"><label class="letmeknow mx-2" for="letmeknow">@lang('search-request.let_me_know')</label>
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>
                @endif

                @if($page == 5)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">4</span><span>/7</span>
                    </div>
                </div>

                    <div class="row ">

                        <div class="col-12 text-center">
                            <p class="text-dark fw-bold">@lang('search-request.do_you_like')</p>
                            <button type="button" class="btn btn-outline-theme my-1" wire:click="next('yes')">@lang('search-request.yes')</button>
                            <button type="button" class="btn btn-outline-theme my-1" wire:click="next('no')">@lang('search-request.no')</button>
                        </div>
                        <div class="my-3">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                        </div>
                    </div>
                @endif

                @if($page == 6)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">5</span><span>/7</span>
                    </div>
                </div>

                    @if($guided)
                        <div class="row">
                            <div class="col-12 text-center">
                                <p class="text-dark fw-bold">@lang('search-request.how_many_days')</p>
                                <button type="button" class="btn btn-outline-theme" wire:click="next('everyday')">@lang('search-request.everyday')</button>
                                <button type="button" class="btn btn-outline-theme" wire:click="next('customday')">@lang('search-request.select')</button>
                            </div>
                        </div>
                        <div class="my-3">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                        </div>
                    @else
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                            <p class="text-dark fw-bold">@lang('search-request.do_you_want_boat_rental')</p>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('yes')">@lang('search-request.yes')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('no')">@lang('search-request.no')</button>
                        </div>
                    </div>
                    <div class="my-3">
                        <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                    </div>
                    @endif
                @endif

                @if($page == 7 && $guided == true && $daysofguiding == 'customday' )
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">5</span><span>/7</span>
                    </div>
                </div>

                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="form-group">
                            <label class="text-dark fw-bold">@lang('search-request.number_of_days_guided')<span style="color:red;">*</span></label>
                            <input class="form-control @error('guidingdays') is-invalid @enderror"  type="number" wire:model.lazy="guidingdays">
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>
                    </div>

                @endif

                @if($page == 7 && $rent_a_boat == true)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">5</span><span>/7</span>
                    </div>
                </div>

                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="form-group">
                            <label class="text-dark fw-bold">@lang('search-request.boat_rental_days')<span style="color:red;">*</span></label>
                            <input class="form-control @error('boat_rental_days') is-invalid @enderror"  type="number" wire:model.lazy="boat_rental_days">
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>
                    </div>
                @endif

                @if($page == 7 && $daysofguiding == 'everyday' && $guided == true)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">6</span><span>/7</span>
                    </div>
                </div>

                <div class="row">
                    <p class="text-dark fw-bold">@lang('search-request.total_budget')<span style="color:red;">*</span></p>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-xl-3">
                        <div class="input-group mb-3">
                            <input type="number"  class="form-control @error('budgetToSpend') is-invalid @enderror" wire:model.lazy="budgetToSpend">
                            <div class="input-group-append">
                              <span class="input-group-text">€</span>
                            </div>
                        </div>
                    </div>
                    <div class="my-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                        <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                    </div>
                </div>
                @endif

                @if($page == 7 && $guided == false && $rent_a_boat == false)
                    <div class="d-flex">
                        <div class="counter">
                            <span class="h2">6</span><span>/7</span>
                        </div>
                    </div>

                    <div class="row">
                        <p class="text-dark fw-bold">@lang('search-request.total_budget')<span style="color:red;">*</span></p>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-xl-3">
                            <div class="input-group mb-3">
                                <input type="number"  class="form-control @error('budgetToSpend') is-invalid @enderror" wire:model.lazy="budgetToSpend">
                                <div class="input-group-append">
                                  <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                        <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                    </div>
                @endif

                @if($page == 8 && $daysofguiding != 'everyday' && $guided == true)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">6</span><span>/7</span>
                    </div>
                </div>
                    <div class="row">
                        <p class="text-dark fw-bold">@lang('search-request.total_budget')<span style="color:red;">*</span></p>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-xl-3">
                            <div class="input-group mb-3">
                                <input type="number"  class="form-control @error('budgetToSpend') is-invalid @enderror" wire:model.lazy="budgetToSpend">
                                <div class="input-group-append">
                                <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                        <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                    </div>

                @endif

                @if($page == 8 && $daysofguiding == 'everyday' && $guided == true)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">7</span><span>/7</span>
                    </div>
                </div>

                @include('pages.partials.contact_form')
                @endif

                @if($page == 8 && $rent_a_boat == true)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">6</span><span>/7</span>
                    </div>
                </div>

                <div class="row">
                    <p class="text-dark fw-bold">@lang('search-request.total_budget')<span style="color:red;">*</span></p>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-xl-3">
                        <div class="input-group mb-3">
                            <input type="number"  class="form-control @error('budgetToSpend') is-invalid @enderror" wire:model.lazy="budgetToSpend">
                            <div class="input-group-append">
                            <span class="input-group-text">€</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="my-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                    <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                </div>
                @endif
                @if($page == 8 && $guided == false && $rent_a_boat == false)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">7</span><span>/7</span>
                    </div>
                </div>

                    @include('pages.partials.contact_form')
                @endif

                @if($page == 9 && $rent_a_boat == false)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">7</span><span>/7</span>
                    </div>
                </div>

                    @include('pages.partials.contact_form')
                @endif
                @if($page == 9 && $rent_a_boat == true)
                <div class="d-flex">
                    <div class="counter">
                        <span class="h2">7</span><span>/7</span>
                    </div>
                </div>

                    @include('pages.partials.contact_form')
                @endif
            @endif
            @if($fishingType == 'tour')
                    @if($page == 2)
                    <div class="d-flex">
                        <div class="counter">
                            <span class="h2">1</span><span>/5</span>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                                <p class="text-dark fw-bold">@lang('search-request.country')<span style="color:red;">*</span></p>
                                <input type="text" class="form-control  @error('country') is-invalid @enderror"  wire:model.lazy="country">
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                                <p class="text-dark fw-bold">@lang('search-request.region')<span style="color:red;">*</span></p>
                                <input type="text" class="form-control  @error('region') is-invalid @enderror"  wire:model.lazy="region">
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>
                    @endif

                    @if($page == 3)
                    <div class="d-flex">
                        <div class="counter">
                            <span class="h2">2</span><span>/5</span>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 my-3">
                                <p class="text-dark fw-bold">@lang('search-request.target_fish')<span style="color:red;">*</span></p>
                                <input type="text" class="form-control  @error('target_fish') is-invalid @enderror"  wire:model.lazy="target_fish">
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 my-3">
                                <p class="text-dark fw-bold">@lang('search-request.num_of_guest')<span style="color:red;">*</span></p>
                                <input class="form-control @error('number_of_guest') is-invalid @enderror"  type="number" wire:model.lazy="number_of_guest">
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>
                    @endif

                    @if ($page == 4)
                    <div class="d-flex">
                        <div class="counter">
                            <span class="h2">3</span><span>/5</span>
                        </div>
                    </div>
                        <div class="row" >
                            @if($calendar)
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 date-wrapper">
                                    <div>
                                        <p class="text-dark fw-bold date-wrapper">@lang('search-request.time_frame')<span style="color:red;">*</span></p>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="me-2">
                                            From: @if($date_from)<span class="color-primary">{{ \Carbon\Carbon::parse($date_from)->format('F j, Y') }}</span>@else - @endif
                                        </div>
                                        <div>
                                            To: @if($date_to)<span class="color-primary">{{ \Carbon\Carbon::parse($date_to)->format('F j, Y') }}</span>@else - @endif
                                        </div>

                                    </div>

                                    <div id="date-from" wire:ignore>

                                    </div>

                                </div>
                            @endif
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 my-3">
                                <input type="checkbox" id="letmeknow" {{$letmeknow ? 'checked' : ''}} wire:click="letMeKnow"><label class="letmeknow mx-2" for="letmeknow">@lang('search-request.let_me_know')</label>
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>
                    @endif

                    @if($page == 5)
                    <div class="d-flex">
                        <div class="counter">
                            <span class="h2">4</span><span>/5</span>
                        </div>
                    </div>
                        <div class="row">
                            <p class="text-dark fw-bold">@lang('search-request.total_budget')<span style="color:red;">*</span></p>
                            <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-xl-3">
                                <div class="input-group mb-3">
                                    <input type="number"  class="form-control @error('budgetToSpend') is-invalid @enderror" wire:model.lazy="budgetToSpend">
                                    <div class="input-group-append">
                                      <span class="input-group-text">€</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="my-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                        </div>

                    @endif

                    @if($page == 6)
                    <div class="d-flex">
                        <div class="counter">
                            <span class="h2">5</span><span>/5</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            @include('pages.partials.contact_form')
                        </div>
                    </div>
                    @endif
            @endif

        </div>


    </form>
</div>

@push('js_push')
    <script>
        $('#target_fish').select2();
    </script>
    <script>
            const initCheckNumberOfColumns = () => {
                if (window.innerWidth > 1000) {
                    return 4;
                } else if (window.innerWidth > 768) {
                    return 2;
                } else {
                    return 1;
                }
            }

            let letmeknow = false;
            var currentDate = new Date();
            currentDate.setDate(currentDate.getDate() + 1); // Set to the next day
            var currentDateString = currentDate.getFullYear() + '-' + (currentDate.getMonth() + 1) + '-' + currentDate.getDate();


            function initializeLitepicker() {
                const dateFrom = document.getElementById('date-from');
                const dateTo = document.getElementById('date-to');
                if (dateFrom) {
                    const dateFromPicker = new Litepicker({
                        element: dateFrom,
                        inlineMode: true,
                        singleMode:false,
                        allowRepick: true,
                        startDate:  @this.date_from,
                        endDate: @this.date_to,
                        minDate: currentDateString,
                        numberOfColumns: initCheckNumberOfColumns(), // Make sure initCheckNumberOfColumns() is defined and returns a valid value
                        numberOfMonths: initCheckNumberOfColumns(), // Assuming initCheckNumberOfColumns() returns the same value for both
                        lang: '{{app()->getLocale()}}',
                        setup: (dateFromPicker) => {

                            dateFromPicker.on('selected', (date1, date2) => {
                                @this.date_from = date1.format('YYYY-MM-DD');
                                @this.date_to = date2.format('YYYY-MM-DD');
                            });
                            window.addEventListener('resize', () => {
                                dateFromPicker.setOptions({
                                    numberOfColumns: initCheckNumberOfColumns(),
                                    numberOfMonths: initCheckNumberOfColumns()
                                });
                            });

                        },
                    });

                }
            }
        window.addEventListener('initDatePicker', event => {
            initializeLitepicker();
        })

        window.addEventListener('dispatchLetMeKnow', event => {
            if(!letmeknow){
                letmeknow = true;
                @this.calendar = false;

            }else{
                @this.calendar = true;
                letmeknow = false;
            }
        })






    </script>
@endpush
