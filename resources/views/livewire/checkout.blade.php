<div>
    {{-- wire:loading wire:target="checkout" --}}
    <div wire:loading wire:target="checkout" class="overlay-container">
        <div class="overlay">
            <div class="spinner">
              <div class="spinner-icon" style="background-image: url({{asset('/assets/images/fish.png')}})"></div>
            </div>
            <div class="message">
              Please wait while processing...
            </div>
        </div>
    </div>

    <div id="all">
        <div id="content">
            <div class="container shadow-lg rounded p-0 my-4">
                <div class="card px-0 pt-5 pb-0 mt-3">
                    <div class="text-center">   
                        <div class="my-2">
                            <h2><strong>Checkout</strong></h2>
                        </div>
                        <div>
                            <p><span class="btn theme-primary text-white">Catchaguide.com</span></p>
                        </div>
                   
                     
                    </div>
    
                    <ul id="progressbar">
                        <li class="active" id="account"><strong>Information</strong></li>
                        <li class=" {{ $this->page === 2 ? 'active' : '' }}"  id="personal"><strong>Order Overview</strong></li>
                    </ul>
                </div>
             
                <div class="row p-4">
                    <div class="col-md-12 clearfix" id="checkout">
                        <div class="border-0">
                            <form wire:submit.prevent="checkout" method="POST">
                      
                                <div class="tab-content" id="pills-tabContent">
      
                                    <div class="tab-pane fade {{ $this->page === 1 ? 'show active' : '' }}" id="pills-home" role="tabpanel"
                                         aria-labelledby="pills-home-tab">

                                        <div class="row">
                                            <div class="col-md-6 my-3">
                                                <div class="my-4">
                                                    <span class="fw-bold">Select Date</span>
                                                </div>
                                               
                                                <div class="col-md-12 d-flex justify-content-center">
                                                    <div id="lite-datepicker"  wire:ignore></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 my-3">
                                                <div class="my-4">
                                                    <span class="fw-bold">Personal Information</span>
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
                                         <div class="row d-flex justify-content-center checkout-container">
                          
                                            <div class="col-lg-8 col-md-8 col-sm-5 my-2">
                                                
                                                <div class="row">
                                                    <div class="col-md-4 my-2">
                                                        @if(get_featured_image_link($guiding))
                                                        <img src="{{get_featured_image_link($guiding)}}" class="card-img-top">
                                                        @else
                                                            <img src="{{asset('images/placeholder_guide.jpg')}}" class="card-img-top">
                                                        @endif
                                                    </div>
                                                    <div class="col-md-8 my-2">
                                                        <div class="card">
                                                            <div class="card-body p-0">
                                                                <h5 class="card-title">{{$guiding->title}}</h5>
                                                                <div  class="d-flex align-items-center">
                                                                    <div>
                                                                        @if($guiding->user->profil_image)
                                                                            <img class="rounded-circle"
                                                                                src="{{asset('images/'. $guiding->user->profil_image)}}" alt="" width="24" height="24">
                                                                        @else
                                                                            <img class="rounded-circle"
                                                                                src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="24" height="24">
                                                                        @endif
                                                                    </div>
                                                                    <div class="mx-2">
                                                                      <span>{{$guiding->user->firstname}}</span>
                                                                    </div>
                                                                </div>

                                                                <div class="guide-description-checkout my-2">
                                                                    <p>{!!$guiding->description!!}</p>
                                                                </div>
                                                    
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                

                                                <div class="guide-info mt-3">
                                                    <div class="mb-2">
                                                        <h5><span class="bordered-heading">Guiding Information</span></h5>
                                                    </div>
                                                    <div class="p-3 bg-light rounded">
                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.duration'):</span>
                                                            </div>
                            
                                                            <div class="px-2 text-dark">
                                                                <p>{{$guiding->duration}} @lang('message.hours')</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.targetFish'):</span>
                                                            </div>
    
                                                            <div class="px-2 text-dark">
                                                                @php 
                                                                $guidingTargets = $targets->pluck('name')->toArray();
        
                                                                if(app()->getLocale() == 'en'){
                                                                    $guidingTargets = $targets->pluck('name_en')->toArray();
                                                                }
                                                            @endphp
                                                            <p>
                                                                {{implode(', ',$guidingTargets)}}
                                                            </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.angelType'):</span>
                                                            </div>
                                   
                                                            <div class="px-2 text-dark">
                                                                @if(app()->getLocale() == 'en')
                                                                {{$guiding->fishingTypes ? $guiding->fishingTypes->name_en : $guiding->fishingTypes->name }}
                                                                @else
                                                                <p>
                                                                    {{$guiding->fishingTypes ? $guiding->fishingTypes->name : '' }}
                                                                </p>
                                                              
                                                                @endif
                                                            </div>
                                                        </div>
    
                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.techniqueMethod'):</span>
                                                            </div>
                                   
                                                            <div class="px-2 text-dark">
                                                                @php 
                                                                $guidingMethods = $methods->pluck('name')->toArray();
    
                                                                if(app()->getLocale() == 'en'){
                                                                    $guidingMethods = $methods->pluck('name_en')->toArray();
                                                                }
                                                                @endphp
    
                                                                {{implode(', ',$guidingMethods)}}
                                                            </div>
                                                        </div>
    
                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.WhereFrom'):</span>
                                                            </div>
                                   
                                                            <div class="px-2 text-dark">
                                                                @if(app()->getLocale() == 'en')
                                                                {{$guiding->fishingFrom->name_en ? $guiding->fishingFrom->name_en : $guiding->fishingFrom->name}}
                                                                @else
                                                                {{$guiding->fishingFrom ? $guiding->fishingFrom->name : ''}}
                                                                @endif
                                                            </div>
                                                        </div>
    
                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.waterType'):</span>
                                                            </div>
                                   
                                                            <div class="px-2 text-dark">
                                                                @php 
                                                                $guidingWaters = $waters->pluck('name')->toArray();
    
                                                                if(app()->getLocale() == 'en'){
                                                                    $guidingWaters = $waters->pluck('name_en')->toArray();
                                                                }
                                                                @endphp
    
                                                                {{implode(', ',$guidingWaters)}}
                                                            </div>
                                                        </div>
    
                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.inclussion'):</span>
                                                            </div>
                                   
                                                            <div class="px-2 text-dark">
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
                                                            </div>
                                                        </div>

                                                        <div class="flex-column border-bottom">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.location'):</span>
                                                            </div>
                                                            <div class="px-2 text-dark">
                                                               {{$guiding->location}}
                                                            </div>
                                                        </div>
                            
    
                                                        <div class="flex-column">
                                                            <div class="my-2">
                                                                <span class="text-dark fw-bold">@lang('profile.meetingPoint'):</span>
                                                            </div>
                                                            <div class="px-2 text-dark">
                                                                {{$guiding->meeting_point ? $guiding->meeting_point : null }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                  
                                                </div>
                                            </div>
                                            <div class="col-sm-3 col-md-4 mobile my-2">
                                                <div class="row">
                                                    <div class="shadow-sm p-3 rounded d-flex flex-column">
                                                        <div class="ml-3"><h5>@lang('message.booking-overview')</h5></div>
                                                        <div class="my-2">
                                                            <div class="px-2 py-1 d-flex">
                                                                <div class="col-8">@lang('message.total-guest'):</div>
                                                                <div class="ml-auto">{{$persons}}</div>
                                                            </div>
                                                            <div class="px-2 py-1 d-flex">
                                                                <div class="col-8">@lang('message.booking-date'):</div>
                                                                <div class="ml-auto">{{$formattedDate}}</div>
                                                             
                                                            </div>
                                                            <div class="border-top px-4 mx-3"></div>
                                                            <div class="px-2 py-1 d-flex">
                                                                <div class="col-8">@lang('message.guiding-price'):</div>
                                                                <div class="ml-auto">€{{$guidingprice}}</div>
                                                            </div>
                                                            @if(count($extras))
                                                            <div class="px-2 py-1 mb-2 bg-light">
                                                                <div>
                                                                    <span class="color-primary">Extras</span>
                                                                </div>
                                                                <div class="row p-2">
                                                                    <div>
                                                                        @foreach($extras as $extra)
                                                                        <div class="col-md-12">
                                                                            <div class="d-flex flex-column">
                                                                                <div class="form-check p-0">
                                                                                    <label class="form-check-label text-dark">
                                                                                        <input type="checkbox" class="form-check-input me-1" name="extra" value="{{$extra->id}}" wire:model.lazy="selectedExtras.{{$extra->id}}">
                                                                                        {{$extra->name}} - €{{$extra->price}}
                                                                                    </label>
                                                                                </div>
                                                                                <div>
                                                                                    @if(count($selectedExtras))
                                                                                        @if(in_array($extra->id,$selectedExtras))
                                                                                        <div class="d-flex align-items-center mb-2">
                                                                                            <label for="">Quantity:</label>
                                                                                            <input id="numericInput" class="w-25 form-control  form-control-sm mx-2" type="number" min="0" step="1" name="quantity" value="2" max="{{$persons}}" wire:model="extraQuantities.{{ $extra->id }}" wire:change="calculateTotalPrice" required>
                                                                                        </div>
                                                                                        @endif
                                                                                    @endif
                                                                                </div>
                                                                            </div>
        
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            <div class="px-2 py-1 d-flex">
                                                                <div class="col-8">@lang('message.total-extras'):</span></div>
                                                                <div class="ml-auto">€{{$totalExtraPrice}}</div>
                                                            </div>
                                                            <div class="border-top px-4 mx-3"></div>
                                                            <div class="px-2 py-1 d-flex pt-3">
                                                                <div class="col-8"><b>Total</b></div>
                                                                <div class="ml-auto"><b class="green">€{{$totalPrice}}</b></div>
                                                            </div>
                                                        </div>
                               
                                                    </div>
                                                    <div class="alert alert-success mt-3" role="alert">
                                                        @lang('forms.total')
                                                        {{$totalPrice}}€
                                                        @lang('forms.total2')
                                                    </div>
                                                        <button class="btn thm-btn rounded border mt-1 p-3" type="submit">@lang('message.complete-booking')</button>
                                                        <button class="btn thm-btn rounded border mt-1 p-3" type="button" wire:click="prev" >@lang('message.return')</button>
                                                        <a class="btn btn-primary rounded border mt-1 p-3" href="{{route('guidings.index')}}" >@lang('message.booking-cancel')</a>
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
                                    <small class="text-danger">@lang('message.error-msg')</small>
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
<script>
    var numericInput = document.getElementById("numericInput");
    // Listen for input events
    numericInput.addEventListener("input", function() {
        // Remove non-numeric characters using a regular expression
        this.value = this.value.replace(/[^0-9]/g, "");
    });
</script>
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
        .litepicker .container__days .day-item.is-today.is-locked{
            color: #fff !important;
            background: green !important;
        
        }

        .litepicker .container__days .day-item.is-start-date.is-end-date{
            color: #fff !important;
        }

        .litepicker .container__days .day-item.is-locked{
            text-decoration: line-through gray;
            color: rgb(76, 76, 76) !important;
        }
        .litepicker .container__days .day-item{
            color:green !important;
        }
    
        .availableEvent {
            border: 1px solid rgb(202, 202, 202);
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
