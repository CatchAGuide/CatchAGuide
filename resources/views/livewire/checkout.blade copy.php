<div>
    <div id="all">
        <div id="content">
            <div class="container my-4">
                <div class="row">
                    <div class="col-md-12 clearfix" id="checkout">
                        <div class="box border-0">
                            <form wire:submit.prevent="checkout" method="POST">

                                <ul class="nav nav-pills mb-3 nav-justified" id="pills-tab" role="tablist">

                                    <li class="nav-item" id="tab1">
                                        <span class="{{ $this->page === 1 ? 'active' : '' }}"><i
                                                    class="fa fa-calendar"></i><br>{{!$agent->isMobile() ? __('message.appointment') : ''}}</span>
                                    </li>

                                    <li class="nav-item" id="tab1">
                                        <span class="{{ $this->page === 2 ? 'active' : '' }}"><i
                                                class="fa fa-map-marker"></i><br>{{!$agent->isMobile() ? __('message.personal-data') : ''}}</span>
                                    </li>
                                    <li class="nav-item" id="tab1">
                                        <span aria-selected="true" class="{{ $this->page === 3 ? 'active' : '' }}"><i
                                                class="fa fa-eye"></i><br>{{!$agent->isMobile() ? __('message.order-overview') : ''}}</span>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade {{ $this->page === 1 ? 'show active' : '' }}" id="pills-payments" role="tabpanel"
                                         aria-labelledby="pills--tab">
                                        <div class="row">
                                            <div class="col-md-12" style="display: flex; justify-content: center;">
                                                <div id="lite-datepicker" wire:ignore></div>
                                            </div>
                                     
                                        </div>
                                       
                                        @foreach($extras as $extra)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model.lazy="selectedExtras" value="{{$extra->id}}" id="flexCheckDefault">
                                            <label class="form-check-label" for="{{$extra->name}}">
                                                {{$extra->name}} - {{$extra->price}}
                                            </label>
                                        </div>
                                        @endforeach
                                    

                                  
                                    </div>

                                    <div class="tab-pane fade {{ $this->page === 2 ? 'show active' : '' }}" id="pills-home" role="tabpanel"
                                         aria-labelledby="pills-home-tab">

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="salutation">@lang('forms.salut')</label>
                                                    <Select type="text" class="form-control" id="salutation" wire:model="userData.salutation">
                                                        <option value="male">Herr</option>
                                                        <option value="female">Frau</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="title">@lang('forms.title')</label>
                                                    <input type="text" class="form-control" id="title" wire:model="userData.title">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.row -->
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="firstname">@lang('forms.name')<span style="color: #e8604c !important;">*</span></label>
                                                    <input type="text" class="form-control" id="firstname" wire:model="userData.firstname" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="lastname">@lang('forms.lastName')<span style="color: #e8604c !important">*</span></label>
                                                    <input type="text" class="form-control" id="lastname" wire:model="userData.lastname" required>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.row -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="street">@lang('forms.street')<span style="color: #e8604c !important">*</span></label>
                                                    <input type="text" class="form-control" id="street" wire:model="userData.address" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="zip">@lang('forms.postal')<span style="color: #e8604c !important">*</span></label>
                                                    <input type="text" class="form-control" id="zip" wire:model="userData.postal" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-8">
                                                <div class="form-group">
                                                    <label for="city">@lang('forms.loc')<span style="color: #e8604c !important">*</span></label>
                                                    <input type="text" class="form-control" id="city" wire:model="userData.city" required>
                                                </div>
                                            </div>


                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="country">@lang('forms.land')</label>
                                                    <input type="text" class="form-control" id="country" wire:model="userData.country">

                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="phone">@lang('forms.pNumber').<span style="color: #e8604c !important; font-size: 12px;">* @lang('forms.pNumberMsg')</span></label>
                                                    <input type="text" class="form-control @error('userData.phone') is-invalid @enderror" id="phone" wire:model="userData.phone" name="userData.phone" value="{{ old('userData.phone') }}" required>
                                                </div>

                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="email">E-Mail<span style="color: #e8604c">*</span></label>
                                                    <input type="text" class="form-control" id="email" wire:model="userData.email" required>
                                                </div>
                                            </div>
                                        </div>

                                    </div>



                                    <div class="tab-pane fade {{ $this->page === 3 ? 'show active' : '' }}" id="pills-orders" role="tabpanel"
                                         aria-labelledby="pills--tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h3 class="tour-details-two__title" style="margin-bottom: 0px;">{{$guiding->title}}</h3>
                                                <span style="color: red !important; font-size: 12px !important;">* @lang('forms.guidTitleMsg')</span>
                                            </div>

                                            <div class="col-md-12 table-responsive" style="margin-top: 40px;">
                                                <table class="table">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col">@lang('forms.date')</th>
                                                        <th scope="col">@lang('forms.guests')</th>
                                                        <th scope="col">@lang('forms.timeLeangth')</th>
                                                        <th scope="col">@lang('forms.price')</th>
                                                        <th scope="col">Extras</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <th>{{date('d.m.Y', strtotime($selectedDate))}}</th>
                                                        <td>{{$persons}}</td>
                                                        <td>
                                                            {{ $guiding->duration }}
                                                            @if($guiding->duration > 1)
                                                                Stunden
                                                            @else
                                                                Stunde
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($persons == 1)
                                                                {{$guiding->price}} €
                                                            @elseif($persons == 2)
                                                                {{$guiding->price_two_persons}} €
                                                            @elseif($persons == 3)
                                                                {{$guiding->price_three_persons}} €
                                                            @elseif($persons == 4)
                                                                {{$guiding->price_four_persons}} €
                                                            @elseif($persons == 5)
                                                                {{$guiding->price_five_persons}} €
                                                            @endif
                                                        </td>
                                                        <td>{{}}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-12" style="margin-top: 50px; font-size: 18px; color: #787780">
                                                @if($guiding->additional_information)
                                                    <br>Sonstiges:</br>
                                                    {{$guiding->additional_information}}
                                                @endif
                                            </div>
                                            <div class="col-md-12" style="margin-top: 50px;">
                                                {{$guiding->description}}
                                            </div>
                                            <div class="col-md-6">
                                                <div class="about-one__right mt-5 pt-2" style="margin-left: 0px;">
                                                    <ul class="list-unstyled tour-details-two__overview-bottom-list">
                                                        <li>
                                                            <div class="icon">
                                                                <i class="fa fa-check"></i>
                                                            </div>
                                                            <div class="text">
                                                                <p><b>Zielfisch/e:</b>
                                                                    @php 
                                                                        $guidingTargets = $targets->pluck('name')->toArray();

                                                                        if(app()->getLocale() == 'en'){
                                                                            $guidingTargets = $targets->pluck('name_en')->toArray();
                                                                        }
                                                                    @endphp

                                                                    {{implode(',',$guidingTargets)}}
                                                                </p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="icon">
                                                                <i class="fa fa-check"></i>
                                                            </div>
                                                            <div class="text">
                                                                <p><b>Angel Art:</b>
                                                                    @if(app()->getLocale() == 'en')
                                                                    {{$guiding->fishingTypes->name_en ? $guiding->fishingTypes->name_en : $guiding->fishingTypes->name }}
                                                                    @else
                                                                    {{$guiding->fishingTypes->name}}
                                                                    @endif
                                                              
                                                                </p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="icon">
                                                                <i class="fa fa-check"></i>
                                                            </div>
                                                            <div class="text">
                                                                <p><b>Technik:</b>
                                                                    @php 
                                                                    $guidingMethods = $methods->pluck('name')->toArray();

                                                                    if(app()->getLocale() == 'en'){
                                                                        $guidingMethods = $methods->pluck('name_en')->toArray();
                                                                    }
                                                                    @endphp

                                                                    {{implode(',',$guidingMethods)}}
                                                                </p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="icon">
                                                                <i class="fa fa-check"></i>
                                                            </div>
                                                            <div class="text">
                                                                <p><b>Ufer / Boot:</b>
                                                                    @if(app()->getLocale() == 'en')
                                                                    {{$guiding->fishingFrom->name_en ? $guiding->fishingFrom->name_en : $guiding->fishingFrom->name}}
                                                                    @else
                                                                    {{$guiding->fishingFrom->name}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </li>
                                                        @if($guiding->meeting_point)
                                                            <li>
                                                                <div class="icon">
                                                                    <i class="fa fa-check"></i>
                                                                </div>
                                                                <div class="text">
                                                                    <p><b>Treffpunkt:</b>
                                                                        {{$guiding->meeting_point}}
                                                                    </p>
                                                                </div>
                                                            </li>
                                                        @endif

                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-5 pt-2 text-left">
                                                    <ul class="list-unstyled tour-details-two__overview-bottom-list" >
                                                        <li>
                                                            <div class="icon" style="margin-bottom: auto;">
                                                                <i class="fa fa-check"></i>
                                                            </div>
                                                            <div class="text">
                                                                <p><b>Gewässer Typ:</b>
                                                                    @php 
                                                                    $guidingWaters = $waters->pluck('name')->toArray();

                                                                    if(app()->getLocale() == 'en'){
                                                                        $guidingWaters = $waters->pluck('name_en')->toArray();
                                                                    }
                                                                    @endphp

                                                                    {{implode(',',$guidingWaters)}}

                                                                </p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="icon" style="margin-bottom: auto;">
                                                                <i class="fa fa-check"></i>
                                                            </div>
                                                            <div class="text">
                                                                <p><b>Gast-/Gewässerkarte:</b>
                                                                    {{$guiding->required_special_license ? $guiding->required_special_license : 'Nein'}}
                                                                </p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="icon" style="margin-bottom: auto;">
                                                                <i class="fa fa-check"></i>
                                                            </div>
                                                            <div class="text">
                                                                <p><b>Equipment:</b>
                                                                    @if($guiding->equipmentStatus->id == 2)
                                                                    {{$guiding->needed_equipment}}
                                                                    @else
                                                                    {{$guiding->equipmentStatus->name}}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </li>
                                                        @if($guiding->needed_equipment)
                                                            <li>
                                                                <div class="icon" style="margin-bottom: auto;">
                                                                    <i class="fa fa-check"></i>
                                                                </div>
                                                                <div class="text">
                                                                    <p><b>Bitte mitbringen:</b>
                                                                        {{$guiding->needed_equipment}}
                                                                    </p>
                                                                </div>
                                                            </li>
                                                        @endif
                                                        @if($guiding->catering)
                                                            <li>
                                                                <div class="icon" style="margin-bottom: auto;">
                                                                    <i class="fa fa-check"></i>
                                                                </div>
                                                                <div class="text">
                                                                    <p><b>Verpflegung:</b>
                                                                        {{$guiding->catering}}
                                                                    </p>
                                                                </div>
                                                            </li>
                                                        @endif

                                                    </ul>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>
                            @if($this->page === 3)
                                <div class="alert alert-danger" role="alert">
                                    @lang('forms.total')
                                    @if($persons == 1)
                                        {{$guiding->price}} €
                                    @elseif($persons == 2)
                                        {{$guiding->price_two_persons}} €
                                    @elseif($persons == 3)
                                        {{$guiding->price_three_persons}} €
                                    @elseif($persons == 4)
                                        {{$guiding->price_four_persons}} €
                                    @elseif($persons == 5)
                                        {{$guiding->price_five_persons}} €
                                    @endif 
                                    @lang('forms.total2')
                                </div>
                            @endif

                        </div>
                        <div class="box-footer">
                            @if(count($errors) > 0)
                                <div class="alert alert-danger" role="alert">
                                    Du hast einen Fehler in Deinen persönlichen Daten. Achte darauf, dass alle Pflichtfelder korrekt ausgefüllt sind.
                                </div>
                            @endif
                            @if($this->page !== 1)
                                <div class="pull-left">
                                    <button class="thm-btn" wire:click="prev"><i
                                            class="fa fa-chevron-left"></i>@lang('message.return')</button>
                                </div>
                            @endif
                            @if($page === 2)
                                <div class="pull-right">
                                    @if($selectedTime !== null)
                                        <button class="thm-btn" wire:click="next">@lang('message.further') <i
                                                class="fa fa-chevron-right"></i>
                                        </button>
                                    @else
                                        <button class="thm-btn thm-btn-disabled" wire:click="next" disabled>@lang('message.further')<i
                                                class="fa fa-chevron-right"></i>
                                        </button>
                                    @endif
                                </div>
                            @elseif ($page !== 3)
                                <div class="pull-right">
                                    <button class="thm-btn" wire:click="next">@lang('message.further') <i
                                            class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                            @else
                                <div class="pull-right">
                                    <button class="thm-btn" wire:click="checkout">
                                        für
                                        @if($persons == 1)
                                            {{$guiding->price}} €
                                        @elseif($persons == 2)
                                            {{$guiding->price_two_persons}} €
                                        @elseif($persons == 3)
                                            {{$guiding->price_three_persons}} €
                                        @elseif($persons == 4)
                                            {{$guiding->price_four_persons}} €
                                        @elseif($persons == 5)
                                            {{$guiding->price_five_persons}} €
                                        @endif
                                        buchen
                                    </button>
                                </div>
                            @endif
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.col-md-9 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container -->
        </div>
        <!-- /#content -->
    </div>



</div>

@push('js_push')
    <script>

        const initCheckNumberOfMonthsToShow = () => {
            if(window.innerWidth > 1000) {
                return 3;
            } else if (window.innerWidth > 768){
                return 2;
            } else {
                return 1;
            }
        }
        document.addEventListener('livewire:load', function () {
            const picker = new Litepicker({
                element: document.getElementById('lite-datepicker'),
                inlineMode: true,
                singleDate: true,
                // show three columns if desktop but 1 if mobile 
                numberOfColumns: initCheckNumberOfMonthsToShow(),
                numberOfMonths: initCheckNumberOfMonthsToShow(),
                minDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000),

                lockDays: [
                    @foreach($guiding->user->blocked_events as $blocked)
                            ["{{ substr($blocked->from,0,-9) }}","{{ substr($blocked->due,0,-9) }}"],
                    @endforeach
                    , new Date(),
                ],
                // select next day available 
                
                lang: '{{app()->getLocale()}}',
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        // Livewire set date
                        @this.selectedDate = date1.format('YYYY-MM-DD');
                        // Set Date to function
                        $("#currentDate").html(date1.format('DD-MM-YYYY'));
                        @this.setSelectedTime('{{ '00:00' }}');
                    });

                    // change picker in smaller resolutions
                    window.addEventListener('resize', (event) => {
                        if(window.innerWidth > 1000) {
                            picker.setOptions({
                                numberOfColumns: 3,
                                numberOfMonths: 3,
                            });
                        } else if (window.innerWidth > 768) {
                            picker.setOptions({
                                numberOfColumns: 2,
                                numberOfMonths: 2,
                            });
                        } else {
                            picker.setOptions({
                                numberOfColumns: 1,
                                numberOfMonths: 1,
                            });
                        }
                    });

                },

         
            
            })
            // picker.setDate(new Date(), true)
            // Set Date to function
        })
    </script>
@endpush

@section('css_after')
    <style>
        .availableEvent {
            border: 1px solid lightgrey;
            border-radius: 5%;
            padding: 5px 10px;
            cursor: pointer;
            margin-bottom: 5px;
            margin-right: 7px;
            transition: all 0.2s ease-in-out;
        }
        .availableEvent.selected {
            background-color: var(--thm-primary);
            color: white !important;
            border: none;
        }
        .availableEvent:hover {
            background-color: var(--thm-primary);
            color: white !important;
        }
        .leadingEvents {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }

        .is-start-date, .is-end-date {
            color: white !important;
            background-color: var(--thm-success) !important;
        }
        .is-today {
            background-color: var(--thm-danger) !important;
            color: white !important;
        }
        .litepicker .container__days .day-item:hover {
            color: var(--thm-primary);
            -webkit-box-shadow: inset 0 0 0 1px var(--thm-primary);
            box-shadow: inset 0 0 0 1px var(--thm-primary);
        } 
    </style>
@endsection
