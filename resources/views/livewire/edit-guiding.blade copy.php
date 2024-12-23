<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}

    <div wire:loading wire:target="register" class="overlay-container">
        <div class="overlay">
            <div class="spinner">
              <div class="spinner-icon"></div>
            </div>
            <div class="message">
              Please wait while processing...
            </div>
          </div>
    </div>


    <form  id="guideform" wire:submit.prevent="register" method="POST">
    @if($currentStep == 1)
    <div class="row my-2">
        <div class="form-group col-md-12">
            <div class="alert alert-primary" role="alert">
                @lang('profile.onemsg')
            </div>
            <input id="fileInput" type="file" name="images[]" accept="image/png, image/jpeg, image/jpg" multiple="multiple" wire:model.lazy="photos">
        </div>
    </div>
    <span id="imageCount" class="text-danger"></span>
    @if ($photos)
    <div class="row my-2">
        @foreach ($photos as $index => $photo)
            <div class="col-md-4 col-6 guidings-gallery">
                <div class="position-relative my-1">
                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="img-thumbnail">
                    <button type="button" class="btn btn-danger rounded-0 remove-btn btn-sm" wire:click="removePhoto({{ $index }})">x</button>
                </div>
            </div>
        @endforeach
    </div>
    @endif


    <div class="row my-2">
        <div class="form-group col-md-12">
            <label for="title">@lang('profile.guidetitle')<span style="color:red;">*</span>
                <span style="color:red; font-size: 12px;">
                   @lang('profile.guidetitlemsg')
                </span>
            </label>
            <input type="text" class="form-control" id="title" placeholder="@lang('profile.guidetitle')" wire:model.lazy="title" name="title" required>
        </div>
    </div>
    
    @error('title') <span class="text-danger">{{$message}}</span>@enderror

    <div class="row my-2">
        <div class="form-group col-md-12" >
            <label for="searchPlace">@lang('profile.location')<span style="color:red;">*</span></label>
            <div>
                <input type="text" class="form-control" id="searchPlace" placeholder="@lang('profile.location')" wire:model.lazy="selectedPlace" autocomplete="off" name="location" required>
                <input type="hidden" id="placeLat" wire:model.lazy="lat" value="{{$guiding->lat}}" name="lat"/>
                <input type="hidden" id="placeLng" wire:model.lazy="lng" value="{{$guiding->lng}}"  name="lng"/>
            </div>

        </div>
    </div>
    @error('location') <span class="text-danger">{{$message}}</span>@enderror
    {{-- end step 1--}}
    @endif

    @if($currentStep == 2)
    {{-- step 2--}}
    <div class="row my-2">
        <div class="form-group col-md-12 my-2">
            <h5 class="my-2">@lang('profile.designfor')<span style="color:red;">*</span></h5>
                @foreach($levels as $key => $level)
                    <input class="form-check-input" type="checkbox" value="{{$level->id}}" wire:model.lazy="selectedLevels.{{$level->id}}" id="{{$level->name}}"  name="selectedLevels[]">
                    <label class="form-check-label" for="flexCheckDefault">
                        {{$level->name}}
                    </label>
                @endforeach
        </div>

        <div class="form-group col-md-12 my-2">
            <label for="duration">@lang('profile.duration')<span style="color:red;">*</span>
                <span style="color:red; font-size: 12px;">
                   @lang('profile.durationmsg')
                </span>
            </label>
            <input type="number" class="form-control" id="duration" name="duration" wire:model.lazy="duration"  placeholder="@lang('profile.duration')" required>
        </div>
        @error('duration') <span class="text-danger">{{$message}}</span>@enderror
    </div>

    <div class="row my-2">
        <h4 style="margin-bottom: 15px;">Inbegriffen<span style="color:red;">*</span></h4>
        <div class="form-group col-md-12">
            @foreach($allinclussions as $key => $inclussion)
            <input class="form-check-input" type="checkbox" value="{{$inclussion->id}}" id="{{$inclussion->name}}" wire:model.lazy="inclussions.{{ $inclussion->id }}" name="inclussions[]">
            <label class="form-check-label" for="{{$inclussion->name}}">
                {{$inclussion->name}}
            </label>
            @endforeach

 
        </div>
    </div>

    <div class="row my-2">
        <div class="form-group col-md-12 my-2">
            <label for="required_special_license">@lang('profile.specificguestcard')<span style="color:red;">*</span>
                <br>
                <span style="color:red; font-size: 12px;">
                   @lang('profile.specificguestcardmsg')
                </span>
            </label>
            {{$guiding->required_special_license}}
            <select class="form-control" id="special_license_needed" wire:model.lazy="special_license_needed"  name="special_license_needed" required>
                <option value="Nein">@lang('profile.no')</option>
                <option value="Ja" >@lang('profile.yes')</option>
            </select>
            @if ($inputVisible)
                <div>
                    <label for="required_special_license">Bitte gib die Gastkarte oder den Gewässerschein an der benötigt wird*</label>
                    <input type="text" class="form-control" id="required_special_license"
                    placeholder="Gewässerkarte/Gewässerschein" wire:model.lazy="required_special_license" name="required_special_license">
                </div>
            @endif
        </div>
    </div>

    <div class="row my-2">
        <div class="form-group col-md-12">
            <label for="water_name">@lang('profile.bodyOfWater')<span style="color:red;">*</span><span style="color:red; font-size: 12px;"> @lang('profile.bodyOfWaterMsg')</span></label>
            <input type="text" class="form-control" id="water_name" wire:model.lazy="water_name" placeholder="@lang('profile.bodyOfWater')" name="water_name">
        </div>
    </div>

    <div class="row my-2">
        <div class="form-group col-md-12">
            <label for="provided_equipment">@lang('profile.meetingPoint')<span style="color:red;">*</span></label>
            <input type="text" class="form-control" id="meeting_point" wire:model.lazy="meeting_point" placeholder="@lang('profile.meetingPoint')"
                   name="meeting_point">
        </div>
    </div>

    <div class="row my-2">
        <div class="form-group col-md-12">
            <label for="additional_information">@lang('profile.four')</label>
            <input type="text" class="form-control" id="additional_information" wire:model.lazy="additional_information" placeholder="@lang('profile.four')"
                   name="additional_information">
        </div>
    </div>
    {{-- end step 2--}}
    @endif

    @if($currentStep == 3)
    <div class="row my-2">
        {{-- <h3>2. @lang('profile.two')</h3> --}}
        <div class="form-group col-md-6 my-2">
            <div class="my-1">
                <h5>@lang('profile.angelType')?<span style="color:red;">*</span></h5>
            </div>
            <select class="form-control @error('fishing_type') is-invalid @enderror" id="fishing_type" wire:model.lazy="fishing_type" name="fishing_type" required>
                <option class="text-muted" selected>Please Select</option>
                @foreach($allfishingtypes as $index => $fishingtype)
                <option value="{{$fishingtype->id}}">{{$fishingtype->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6 mb-3">
            <label for="fishing_from">@lang('profile.WhereFrom')?<span style="color:red;">*</span></label>
            <select class="form-control" id="fishing_from" wire:model.lazy="fishing_from" name="fishing_from" required>
                <option value="0" selected>@lang('profile.WhereFromChoice1')</option>
                <option value="1">@lang('profile.WhereFromChoice2')</option>
            </select>
        </div>
        <div class="form-group col-md-12">
            <label for="required_equipment">@lang('profile.gearandeq')<span style="color:red;">*</span></label>
            <select class="form-control" id="required_equipment" wire:model.lazy="required_equipment"  name="required_equipment">
                <option class="text-muted" selected>Please Select</option>
                @foreach($allequipmentStatus as $status)
                <option value="{{$status->id}}">{{$status->name}}</option>
                @endforeach
            </select>
        </div>

        @if($is_needed)
        <div class="form-group col-md-12 my-2" id="needed_equipment">
            <div class="my-1">
                <h5>@lang('profile.four') Benötigtes Equipment<span style="color:red;">*</span></h5>
            </div>
            <input type="text" class="form-control" placeholder="@lang('profile.four') Benötigtes Equipment" wire:model.lazy="needed_equipment" name="needed_equipment">
        </div>
        @endif

        <div class="form-group col-md-12 my-2">
            <h4 style="margin-bottom: 15px;">@lang('profile.waterType')<span style="color:red;">*</span><span style="color:red; font-size: 12px;">*@lang('profile.waterTypeMsg')</span></h4>
            @foreach($allwaters as $index => $water)
            <input class="form-check-input" type="checkbox" value="{{$water->name}}" id="{{$water->name}}" wire:model.lazy="water" name="water[]">
                @switch(app()->getLocale())
                   @case('de')
                       <label class="form-check-label" for="{{$water->name}}">
                           {{$water->name}}
                       </label>
                   @break;
                   @case('en')
                       <label class="form-check-label" for="{{$water->name}}">
                           {{$water->name_en ? $water->name_en: $water->name}}
                       </label>
                   @break
                   @default
                   {{$water->name}}
               @endswitch
            @endforeach
        </div>
        @error('water') <span class="text-danger">{{$message}}</span>@enderror
        <div class="form-group col-md-12 my-2">
            <label class="form-check-label" for="water_sonstiges">
               @lang('profile.otherWaterTypes')
            </label>
            <input class="form-control" type="text" id="water_sonstiges" placeholder="@lang('profile.otherWaterTypes')" wire:model.lazy="water_sonstiges"  name="water_sonstiges">
        </div>

        <div class="form-group col-md-12 my-2">
            <h4 style="margin-bottom: 15px;">@lang('profile.targetFish')<span style="color:red;">*</span><span style="color:red; font-size: 12px;">*@lang('profile.targetFishMsg')</span></h4>
            <!-- <div class="d-flex flex-grow-1"> -->

                <div class="row">

                    @foreach($alltargets as $target)
                    <div class="col-md-3 col-6">
                        <input class="form-check-input" type="checkbox" value="{{$target->name}}"  wire:model.lazy="targets" id="{{$target->name}}" name="targets[]">
                        @switch(app()->getLocale())
                            @case('de')
                                <label class="form-check-label" for="{{$target->name}}">
                                    {{$target->name}}
                                </label>
                            @break
                            @case('en')
                                <label class="form-check-label" for="{{$target->name}}">
                                    {{$target->name_en ? $target->name_en: $target->name}}
                                </label>
                            @break
                            @default
                            {{$target->name}}
                        @endswitch
                    </div>
                    @endforeach

                </div>

                

            <!-- </div> -->
        </div>
        @error('targets') <span class="text-danger">{{$message}}</span>@enderror
        <div class="form-group col-md-12 my-2">
            <label class="form-check-label" for="target_fish_sonstiges">
               @lang('profile.otherTargetFish')
            </label>
            <input class="form-control" type="text" id="target_fish_sonstiges" placeholder="@lang('profile.otherTargetFish')" wire:model.lazy="target_fish_sonstiges" name="target_fish_sonstiges">
        </div>
        <div class="form-group col-md-12 my-2">
            <h4 style="margin-bottom: 15px;">@lang('profile.techniqueMethod')<span style="color:red;">*</span><span style="color:red; font-size: 12px;">*@lang('profile.techniqueMethodMsg')</span></h4>
            @foreach($allmethods as $method)
                <input class="form-check-input" type="checkbox" value="{{$method->name}}" wire:model.lazy="methods" id="{{$method->name}}"
                       name="methods[]">

                    @switch(app()->getLocale())
                       @case('de')
                           <label class="form-check-label" for="{{$method->name}}">
                               {{$method->name}}
                           </label>
                       @break
                       @case('en')
                           <label class="form-check-label" for="{{$method->name}}">
                               {{$method->name_en ? $method->name_en: $method->name}}
                           </label>
                       @break
                       @default
                       {{$method->name}}
                    @endswitch
                <br>
            @endforeach
        </div>
        @error('methods') <span class="text-danger">{{$message}}</span>@enderror
        <div class="form-group col-md-12 my-2">
            <label class="form-check-label" for="methods_sonstiges">
            @lang('profile.otherTechniqueMethod')
            </label>
            <input class="form-control" type="text" id="methods_sonstiges" wire:model.lazy="methods_sonstiges" placeholder="@lang('profile.otherTechniqueMethod')" name="methods_sonstiges">
        </div>
    </div>
    @endif

    @if($currentStep == 4)
    <h3>3. @lang('profile.three')<span style="color:red;">*</span></h3>
    <div class="row">
        <div class="form-group col-md-12">
            <label for="description"><span style="color:red; font-size: 12px;">*@lang('profile.threeMsg')</span></label>
                <textarea wire:model.lazy="description" name="description" id="description" class="w-100 p-2" rows="10"></textarea> 
        </div>
    </div>
    @error('description') <span class="text-danger">{{$message}}</span>@enderror
    @endif

    @if($currentStep == 5)
    <h3>@lang('profile.five')</h3>
    <p>@lang('profile.fees'):<span style="color:red;">*</span><br>
        <span style="color:red; font-size: 12px;">*@lang('profile.feeMsg')</span><br>
        @lang('profile.feeMsg1')<br>
        @lang('profile.feeMsg2')<br>
        @lang('profile.feeMsg3')<br>
    </p>
    <div class="row my-2">
        <div class="form-group col-md-6">
            <label for="max_guests">@lang('profile.maxGuest')<span style="color:red;">*</span></label>
            <select class="form-control" id="max_guests" wire:model.lazy.debounce.{{ $debounce }}ms="max_guests"  name="max_guests" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
    </div>

    <div class="row my-2">
        @if($p1)
        <div class="form-group col-md-4" id="one-person">
            <label for="price">@lang('profile.totalPrice')<span style="color:red;">*</span></label>
            <input type="number" class="form-control" id="price" name="price" wire:model.lazy="price" placeholder="{{ translate('Gesamtpreis für eine Person') }}" required>
        </div>
        @endif

        @if($p2)
            <div class="form-group col-md-4" id="two-person">
                <label for="price_two_persons">{{ translate('Gesamtpreis für zwei Personen') }}</label>
                <input type="number" class="form-control" id="price_two_persons" wire:model.lazy="price_two_persons" placeholder="{{ translate('Gesamtpreis für zwei Personen') }}" name="price_two_persons">
            </div>
        @endif
        
        @if($p3)
            <div class="form-group col-md-4" id="three-person" >
                <label for="price_three_persons">{{ translate('Gesamtpreis für drei Personen') }}</label>
                <input type="number" class="form-control" id="price_three_persons" wire:model.lazy="price_three_persons" placeholder="{{ translate('Gesamtpreis für drei Personen') }}" name="price_three_persons">
            </div>
        @endif
        
        @if($p4)
            <div class="form-group col-md-4" id="four-person" >
                <label for="price_four_persons">{{ translate('Gesamtpreis für vier Personen') }}</label>
                <input type="number" class="form-control" id="price_four_persons" placeholder="{{ translate('Gesamtpreis für vier Personen') }}" name="price_four_persons">
            </div>
        @endif
        
        @if($p5)
            <div class="form-group col-md-4" id="five-person" >
                <label for="price_five_persons">{{ translate('Gesamtpreis für fünf Personen') }}</label>
                <input type="number" class="form-control" id="price_five_persons" placeholder="{{ translate('Gesamtpreis für fünf Personen') }}" name="price_five_persons">
            </div>
        @endif
    </div>

    <div class="row my-2">
        <div class="col-md-12 my-2">
            <div class="d-flex ml-auto align-items-center">
                <div><h4>Extras hinzufügen</h4></div>
                
                <button class="btn btn-primary my-2 mx-3" type="button" wire:click="addExtra"><i class="fa fa-plus" aria-hidden="true"></i> Add</button>
            </div>  

            @foreach ($extras as $key => $extra)
            <div class="row my-2">
                <div class="col-md-4 my-1">
                    <input class="form-control" type="text" wire:model.lazy="extras.{{ $key }}.name" placeholder="Name" required>
                </div>
                <div class="col-md-4 my-1">
                    <input class="form-control" type="number" wire:model.lazy="extras.{{ $key }}.price" placeholder="Price" required>
                </div>
                <div class="col-md-4 my-1">
                    <button class="btn btn-danger" type="button" wire:click="removeExtra({{ $key }})">Remove</button>
                </div>
            </div>
            @endforeach 

        </div>
    </div>
    @endif

    <div class="d-flex my-3">
        @if($currentStep == 1)
        <div></div>
        @endif
        @if($currentStep == 2 || $currentStep == 3 || $currentStep == 4 || $currentStep == 5)
        <button href="#guideform" class="btn btn-primary" wire:loading.attr="disabled" type="button"  wire:click="decreaseStep()">Back</button>
        @endif

        @if($currentStep == 1 || $currentStep == 2 || $currentStep == 3 || $currentStep == 4)
        <button href="#guideform" class="btn btn-success @if($currentStep != 1) mx-2 @endif" wire:loading.attr="disabled" type="button"  wire:click="increaseStep()">Next</button>
        @endif

        @if($currentStep == 5)
            <button class="btn btn-success mx-2" type="button" wire:click="register" wire:loading.attr="disabled">
                <span>Submit</span>
            </button>
      
        @endif
    </div>




</div>



@push('js_push')
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q') }}&libraries=places,geocoding"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>

<script>
  const fileInput = document.getElementById("fileInput");

fileInput.addEventListener("change", (event) => {
  const selectedFiles = event.target.files;
  if (selectedFiles.length > 5) {
    event.preventDefault();
    // Display an error message or handle the exceeding file selection in your desired way
    $('#imageCount').html('You can only select up to 5 files.')
    // Reset the file input value to clear the selection
    fileInput.value = "";
  }
});
</script>

<script>
    Livewire.on('scrollToTop', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });


</script>


<script>


function initializeAutocomplete() {
        var input = document.getElementById('searchPlace');
        var autocomplete = new google.maps.places.Autocomplete(input);

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            let lat = place.geometry.location.lat();
            let lng = place.geometry.location.lng();
            let location = place.formatted_address;
            Livewire.emit('locationSelected', { lat, lng, location });
        });
    }

    document.addEventListener("livewire:load", function () {
        initializeAutocomplete();

        Livewire.on("stepDecreased", function (data) {
                console.log(data.selectedPlace)
            if (data.selectedPlace) {
                Livewire.emit('updateSelectedPlace', data.selectedPlace);
            }
            setTimeout(initializeAutocomplete, 0);
        });

        Livewire.on('stepIncreased', function (selectedPlace) {
        // Update the value of the searchPlace input field
        if (data.selectedPlace) {
                Livewire.emit('updateSelectedPlace', data.selectedPlace);
        }
        
        }); 
    });
</script>
<script>
    document.addEventListener('livewire:load', function () {
            var fileInput = document.getElementById('fileInput');
            fileInput.addEventListener('change', handleFileInput);

            function handleFileInput(event) {
                var selectedFiles = event.target.files;

                var formData = new FormData();

                for (var i = 0; i < selectedFiles.length; i++) {
                    formData.append('photos[]', selectedFiles[i]);
                }

                Livewire.emit('photosSelected', formData);
            }
        });
</script>
<script>
    document.addEventListener('livewire:load', function () {
        $('#max_guests').on('change', function (event) {
            Livewire.emit('maxGuestsChanged', event.target.value);
        });
    });
</script>
@endpush

