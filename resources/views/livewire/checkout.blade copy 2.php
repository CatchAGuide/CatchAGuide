<div>
    <div id="all">
        <div id="content">
            <div class="container my-4 shadow-lg p-4">
                <div class="row">
                    <div class="col-md-12 clearfix" id="checkout">
                        <div class="box border-0">
                            <form wire:submit.prevent="checkout" method="POST">
                      
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="mb-5">
                                        <h4>Checkout</h4>
                                    </div>

                                    <ul id="progressbar">
                                        <li class="active" id="account"><strong>Account</strong></li>
                                        <li id="personal"><strong>Personal</strong></li>
                                        <li id="payment"><strong>Payment</strong></li>
                                        <li id="confirm"><strong>Finish</strong></li>
                                    </ul>
                               
                                    <div class="tab-pane fade {{ $this->page === 1 ? 'show active' : '' }}" id="pills-home" role="tabpanel"
                                         aria-labelledby="pills-home-tab">

                                        <div class="row">
                                            <div class="col-md-6 my-3">
                                                <div class="my-4">
                                                    <h5 class="text-muted">Select Booking Date</h5>
                                                </div>
                                               
                                                <div class="col-md-12 d-flex justify-content-center">
                                                    <div id="lite-datepicker"  wire:ignore></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 my-3">
                                                <div class="my-4">
                                                    <h5 class="text-muted">Personal Information</h5>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="salutation">@lang('forms.salut')</label>
                                                            <Select type="text" class="form-control rounded" id="salutation" wire:model="userData.salutation">
                                                                <option value="male">Herr</option>
                                                                <option value="female">Frau</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="title">@lang('forms.title')</label>
                                                            <input type="text" class="form-control rounded" id="title" wire:model="userData.title">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="firstname">@lang('forms.name')<span style="color: #e8604c !important;">*</span></label>
                                                            <input type="text" class="form-control rounded @error('userData.firstname') is-invalid @enderror" id="firstname" wire:model="userData.firstname" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="lastname">@lang('forms.lastName')<span style="color: #e8604c !important">*</span></label>
                                                            <input type="text" class="form-control rounded  @error('userData.lastname') is-invalid @enderror" id="lastname" wire:model="userData.lastname" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="street">@lang('forms.street')<span style="color: #e8604c !important">*</span></label>
                                                            <input type="text" class="form-control rounded @error('userData.address') is-invalid @enderror" id="street" wire:model="userData.address" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="zip">@lang('forms.postal')<span style="color: #e8604c !important">*</span></label>
                                                            <input type="text" class="form-control rounded @error('userData.postal') is-invalid @enderror" id="zip" wire:model="userData.postal" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 col-md-8">
                                                        <div class="form-group">
                                                            <label for="city">@lang('forms.loc')<span style="color: #e8604c !important">*</span></label>
                                                            <input type="text" class="form-control rounded @error('userData.city') is-invalid @enderror" id="city" wire:model="userData.city" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label for="country">@lang('forms.land')</label>
                                                            <input type="text" class="form-control rounded" id="country" wire:model="userData.country">
        
                                                        </div>
                                                    </div>
        
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label for="phone">@lang('forms.pNumber').<span style="color: #e8604c !important; font-size: 12px;">* @lang('forms.pNumberMsg')</span></label>
                                                            <input type="text" class="form-control rounded @error('userData.phone') is-invalid @enderror" id="phone" wire:model="userData.phone" name="userData.phone" value="{{ old('userData.phone') }}" required>
                                                        </div>
        
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label for="email">E-Mail<span style="color: #e8604c">*</span></label>
                                                            <input type="text" class="form-control rounded" id="email" wire:model="userData.email" required>
                                                        </div>
                                                    </div>
                                                                            
                                                        <div class="col-md-12">
                                                            @if($page === 1)
                                                                <div class="pull-right">
                                                                    @if($selectedTime !== null)
                                                                        <button class="thm-btn" type="button" wire:click="next">@lang('message.further') <i
                                                                                class="fa fa-chevron-right"></i>
                                                                        </button>
                                                                    @else
                                                                        <button class="thm-btn thm-btn-disabled"  type="button"  wire:click="next" disabled>@lang('message.further')<i
                                                                                class="fa fa-chevron-right"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @elseif ($page !== 2)
                                                                <div class="pull-right">
                                                                    <button class="thm-btn" wire:click="next">@lang('message.further') <i
                                                                            class="fa fa-chevron-right"></i>
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                </div>
                                            </div>
                                        
                                         </div>

                           
                                    </div>



                                    <div class="tab-pane fade {{ $this->page === 2 ? 'show active' : '' }}" id="pills-orders" role="tabpanel"
                                         aria-labelledby="pills--tab">
                                         <div class="alert alert-success" role="alert">
                                            @lang('forms.guidTitleMsg')
                                          </div>
                                         <div class="row d-flex justify-content-center">
                          
                                            <div class="col-sm-5 col-md-8">
                                                <div class="border rounded p-4 shadow-sm my-1">
                                                    <h4>{{$guiding->title}}</h4>
                                                    <div class="my-2">
                                                        <p>{{$guiding->description}}</p>
                                                    </div>
                                                </div>

                                                <div class="shadow-sm border rounded p-4">
                                                <table class="table table-striped caption-top">
                                                    <caption class="text-dark fw-bold">Guiding Information</caption>
                                                    <tbody>
                                                      <tr>
                                                        <th style="width:1%" scope="row">Zielfisch/e:</th>
                                                        <td>
                                                            @php 
                                                            $guidingTargets = $targets->pluck('name')->toArray();

                                                            if(app()->getLocale() == 'en'){
                                                                $guidingTargets = $targets->pluck('name_en')->toArray();
                                                            }
                                                        @endphp

                                                        {{implode(',',$guidingTargets)}}
                                                        </td>

                                                      </tr>
                                                      <tr>
                                                        <th style="width:1%" scope="row">Angel Art:</th>
                                                        <td>
                                                            @if(app()->getLocale() == 'en')
                                                            {{$guiding->fishingTypes->name_en ? $guiding->fishingTypes->name_en : $guiding->fishingTypes->name }}
                                                            @else
                                                            {{$guiding->fishingTypes->name}}
                                                            @endif
                                                      
                                                        </td>
                                                      </tr>
                                                      <tr>
                                                        <th style="width:1%"  scope="row">Technik:</th>
                                                        <td>
                                                            @php 
                                                            $guidingMethods = $methods->pluck('name')->toArray();

                                                            if(app()->getLocale() == 'en'){
                                                                $guidingMethods = $methods->pluck('name_en')->toArray();
                                                            }
                                                            @endphp

                                                            {{implode(',',$guidingMethods)}}
                                                        </td>
                                                      </tr>
                                                      <tr>
                                                        <th style="width:25%"  scope="row">Fishing From:</th>
                                                        <td>
                                                                @if(app()->getLocale() == 'en')
                                                                {{$guiding->fishingFrom->name_en ? $guiding->fishingFrom->name_en : $guiding->fishingFrom->name}}
                                                                @else
                                                                {{$guiding->fishingFrom->name}}
                                                                @endif
                                                        </td>
                                                      </tr>
                                                      @if($guiding->meeting_point)
                                                      <tr>
                                                        <th style="width:1%"  scope="row">Treffpunkt:</th>
                                                        <td>
                                                            {{$guiding->meeting_point}}
                                                        </td>
                                                      </tr>
                                                      @endif

                                                    
                                                      <tr>
                                                        <th style="width:1%"  scope="row">Gewässer Typ:</th>
                                                        <td>
                                                            @php 
                                                            $guidingWaters = $waters->pluck('name')->toArray();

                                                            if(app()->getLocale() == 'en'){
                                                                $guidingWaters = $waters->pluck('name_en')->toArray();
                                                            }
                                                            @endphp

                                                            {{implode(',',$guidingWaters)}}
                                                        </td>
                                                      </tr>
                                                      <tr>
                                                        <th style="width:30%"  scope="row">@lang('profile.inclussion'):</th>
                                                        <td>
                                                        @php
                                                            $guidingInclusion = $guiding->inclussions->pluck('name')->toArray();
                                                        
                                                            if (app()->getLocale() == 'en') {
                                                                $guidingInclusion = $guiding->inclussions->pluck('name_en')->toArray();
                                                        
                                                                // If name_en is empty, fallback to name
                                                                foreach ($guidingInclusion as $index => $name) {
                                                                    if (empty($name)) {
                                                                        $guidingInclusion[$index] = $guiding->inclussions[$index]->name;
                                                                    }
                                                                }
                                                            }
                                                        @endphp

                                                        @if (!empty($guidingInclusion))
                                                        {{ implode(', ', array_filter($guidingInclusion)) }}
                                                        @endif
                                                            </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </div>
                                        
                                                <div class="row my-2">
                                                    <div>
                                                        <div class="p-4 border shadow-sm">
                                                        <table class="table table-hover">
                                                            <thead>
                                                              <tr>
                                                                <th scope="col"></th>
                                                                <th scope="col">Name</th>
                                                                <th scope="col">Price</th>
                                                              </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($extras as $extra)
                                                              <tr>
                                                                <th style="width:1%" scope="row"><input class="form-check-input" type="checkbox" wire:model="selectedExtras" value="{{$extra->id}}" id="flexCheckDefault"></th>
                                                                <td>{{$extra->name}}</td>
                                                                <td>€{{$extra->price}}</td>
                                                              </tr>
                                                              @endforeach
                                                            </tbody>
                                                          </table>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-sm-3 col-md-4 mobile">
                                                <div class="row">
                                                    <div class="bg-light shadow-sm p-2 rounded d-flex flex-column">
                                                        <div class="p-2 ml-3"><h4>Order Overview</h4></div>
                                                        <div class="px-2 py-1 d-flex">
                                                            <div class="col-8">Total Guest:</div>
                                                            <div class="ml-auto">{{$persons}}</div>
                                                        </div>
                                                        <div class="px-2 py-1 d-flex">
                                                            <div class="col-8">Booking Date:</div>
                                                            <div class="ml-auto">{{$formattedDate}}</div>
                                                         
                                                        </div>
                                                        <div class="border-top px-4 mx-3"></div>
                                                        <div class="px-2 py-1 d-flex">
                                                            <div class="col-8">Guiding Price:</div>
                                                            <div class="ml-auto">€{{$guidingprice}}</div>
                                                        </div>
                                                        <div class="px-2 py-1 d-flex">
                                                            <div class="col-8">Total Extras:</span></div>
                                                            <div class="ml-auto">€{{$totalExtraPrice}}</div>
                                                        </div>
                                                        <div class="border-top px-4 mx-3"></div>
                                                        <div class="px-2 py-1 d-flex pt-3">
                                                            <div class="col-8"><b>Total</b></div>
                                                            <div class="ml-auto"><b class="green">€{{$totalPrice}}</b></div>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-success mt-3" role="alert">
                                                        @lang('forms.total')
                                                        {{$totalPrice}}
                                                        @lang('forms.total2')
                                                    </div>
                                                        <button class="btn thm-btn rounded border mt-1 p-3" wire:click="checkout">Confirm Booking</button>
                                                        <a class="btn btn-primary rounded border mt-1 p-3" href="{{route('guidings.index')}}" >Cancel</a>
                                                </div>
                                            </div>        
                                        </div>
                                         <!-- end -->
                                    </div>
                                </div>
                            </form>

                            @if($errors->any())
                            <div class="alert alert-danger m-0 p-2 my-2" role="alert">
                                <div class="d-flex flex-column">
                                    <small class="text-danger">Looks like you missed something. Please fill in all required fields with valid information.</small>
                                </div>
                            </div>
                            @enderror

                        {{-- <div class="">
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
                            @endif
                        </div> --}}
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
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    {{-- <script>

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
    </script> --}}
    <script>
        const initCheckNumberOfColumns = () => {
            if (window.innerWidth > 1000) {
                return 2;
            } else if (window.innerWidth > 768) {
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
                numberOfColumns: initCheckNumberOfColumns(),
                numberOfMonths: initCheckNumberOfColumns(),
                minDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000),
                lockDays: [
                    @foreach($guiding->user->blocked_events as $blocked)
                        ["{{ substr($blocked->from,0,-9) }}", "{{ substr($blocked->due,0,-9) }}"],
                    @endforeach
                    new Date(),
                ],
                lang: '{{app()->getLocale()}}',
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        // Livewire set date
                        @this.selectedDate = date1.format('YYYY-MM-DD');
                        // Set Date to function
                        $("#currentDate").html(date1.format('DD-MM-YYYY'));
                        @this.setSelectedTime('{{ '00:00' }}');
                        // Add the selected date to the selected column
                        const selectedColumn = document.querySelector('.litepicker-column.selected');
                        if (selectedColumn) {
                            const selectedDateCell = selectedColumn.querySelector('.litepicker-cell.is-selected');
                            if (selectedDateCell) {
                                selectedDateCell.classList.add('selected-date');
                            }
                        }
                    });
    
                    // Change picker columns on resize
                    window.addEventListener('resize', () => {
                        picker.setOptions({
                            numberOfColumns: initCheckNumberOfColumns(),
                            numberOfMonths: initCheckNumberOfColumns()
                        });
                    });
                },
            });
        });
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
