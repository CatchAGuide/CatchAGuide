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


    <form id="guideform" wire:submit.prevent="register" method="POST">
    @if($currentStep == 1)
    <div class="d-flex align-items-center">
        <div class="bg-secondary p-2 rounded d-flex align-items-center"><h4 class="text-white fw-bold me-1">1</h4><small class="text-white">/5</small></h4>
        </div>
        <div class="mx-3 ">
            <h4>@lang('profile.pgeneral')</h4>
        </div>
    </div>
    <hr>
    {{-- step 1--}}
    {{-- <h3>1. @lang('profile.one')</h3> --}}
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <h5>Featured Image</h5>
                    <input class="form-control" id="featuredImage" type="file" wire:model.lazy="featuredImage" accept="image/png, image/jpeg, image/jpg">
                </div>
                <div class="col-md-12 my-2">
                    @if ($featuredImage)
                    <img width="240" height="240" src="{{ $featuredImage->temporaryUrl() }}" class="img-fluid ${3|rounded-top,rounded-right,rounded-bottom,rounded-left,rounded-circle,|}" alt="">
                    @endif

                </div>
                <div class="col-md-12">
                    @error('featuredImage')
                    <div class="alert alert-danger">Oops! It seems there was an issue with your image upload. Please try again with an image that is at least 800x600 pixels in size.</div>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    <div class="row "
    x-data="{ isUploading: false, progress: 0 }"
    x-on:livewire-upload-start="isUploading = true"
    x-on:livewire-upload-finish="isUploading = false"
    x-on:livewire-upload-error="isUploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress">

        <div class="form-group col-md-12 my-2">
            <h5>Gallery</h5>
            <div class="d-flex justify-content-center rounded p-4" style="border:dashed 1px #084298">
                <div class="d-flex flex-column text-center">
                    <div>
                        <i class="fa fa-image color-primary" style="font-size:64px"></i>
                    </div>

                    <div class="my-2">
                        <label for="">@if(app()->getLocale() == 'en') Please select at least 5 images. @else Füge Deiner Guiding Gallerie bis zu 5 Bilder hinzu. @endif</label>
                    </div>

                    <div>
                        <label for="fileInput" class="btn btn-primary">
                            <i class="fa fa-upload" aria-hidden="true"></i>
                            @if(app()->getLocale() == 'en') Select @else Auswählen @endif
                                <input class="form-control" id="fileInput" type="file" name="images[]" accept="image/png, image/jpeg, image/jpg" multiple="multiple" wire:model.lazy="photos" style="display: none;">
                        </label>
                    </div>
                    <span id="imageCount" class="text-danger"></span>
                    @error('selectedPhotos') <span class="text-danger">{{$message}}</span>@enderror
                </div>
            </div>
            <div class="progress my-2" x-show="isUploading">
                <div class="progress-bar" role="progressbar" :style="'width: ' + progress + '%'" aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
                  Uploading...
                </div>
            </div>

            <div class="my-2">
                <div class="row justify-content-center gallery-container rounded m-1">
                    @if ($selectedPhotos)
                        @foreach ($selectedPhotos as $index => $photo)
                        <div class="col-6 col-sm-6 col-lg-2 col-md-6 my-1">
                            <div class="d-flex flex-column text-center">
                                    <div class="guiding-gallery position-relative">
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="img-thumbnail">
                                        <button type="button" class="btn btn-danger px-2 py-1 rounded remove-btn btn-sm" wire:click="removePhoto({{ $index }})"><small>x</small></button>
                                    </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="d-flex flex-column text-center">
                                <h5 class="text-secondary"><small> @if(app()->getLocale() == 'en') No Available Image @else Erfolgreich hochgeladene Bilder werden hier angezegt @endif</small></h5>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
            @error('photos')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @if(session()->has('invalid_image'))
            <div class="alert alert-danger">{{ session('invalid_image') }}</div>
            @endif

        </div>
    </div>

        {{-- @if ($selectedPhotos)
            <div class="row d-flex justify-content-center bg-gray-400">
                @foreach ($selectedPhotos as $index => $photo)

                <div class="col-lg-2 col-md-6 guidings-gallery">
                    <div class="position-relative my-1">
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="img-thumbnail" width="500" style="max-height:245px">
                        <button type="button" class="btn btn-danger rounded-0 remove-btn btn-sm" wire:click="removePhoto({{ $index }})">x</button>
                    </div>
                </div>


                @endforeach
            </div>
        @endif --}}

    <div class="row">


        <div class="alert alert-primary my-3" role="alert">
            @lang('profile.onemsg')
        </div>

        <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.guidetitle')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">@lang('profile.guidetitlemsg')</span>
            </div>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" placeholder="@lang('profile.guidetitle')" wire:model.lazy="title" name="title" required>
        </div>

        <div class="form-group col-md-12 my-1" >
            <div class="my-1">
                <h5>@lang('profile.location')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;"> @if(app()->getLocale() == 'en') Please provide a location as closely and precisely as possible to the actual guiding water, or city/region. We do not recommend to fill in, e.g. Netherlands, Mallorca, Sweden. @else Bitte wähle den Ort so präzise wie möglich gemäß dem Gewässer oder der Region in der Dein Guiding stattfinden soll. Schlechte Beispiele sind zB. Niederlande, Mallorca, Bayern. @endif</span>
            </div>
            <div>
                <input type="text" class="form-control @error('location') is-invalid @enderror" id="searchPlace" placeholder="@lang('profile.location')" wire:model.lazy="selectedPlace" autocomplete="off" name="location" required>
                <input type="hidden" id="placeLat" wire:model.lazy="lat" name="lat"/>
                <input type="hidden" id="placeLng" wire:model.lazy="lng" name="lng"/>
            </div>
        </div>
    </div>

    {{-- end step 1--}}
    @endif

    @if($currentStep == 2)
    <div class="d-flex align-items-center">
        <div class="bg-secondary p-2 rounded d-flex align-items-center"><h4 class="text-white fw-bold me-1">2</h4><small class="text-white">/5</small></h4>
        </div>
        <div class="mx-3 ">
            <h4>@lang('profile.pgeneral')</h4>
        </div>
    </div>

    <hr>

    {{-- step 2--}}
    <div class="row">
        <div class="form-group col-md-12 my-2">
            <h5>@lang('profile.designfor')<span style="color:red;">*</span></h5>
            <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Multiple selection possible. @else Mehrfachauswahl möglich. @endif</span>
            <div>
                @foreach($levels as $level)
                    <input class="form-check-input @error('selectedLevels') is-invalid @enderror" type="checkbox" wire:model.lazy="selectedLevels.{{$level->id}}" id="{{$level->name}}"  required>
                    <label class="form-check-label" for="flexCheckDefault">
                        {{ getLocalizedValue($level) }}
                    </label>
                @endforeach
            </div>

        </div>

        @error('formessage') <span class="text-danger">{{$message}}</span>@enderror

        <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.duration')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">
                    @lang('profile.durationmsg')
                </span>
            </div>
            <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" wire:model.lazy="duration"  placeholder="@lang('profile.duration')" required>
        </div>

        <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.inclussion')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Multiple selection possible. @else Mehrfachauswahl möglich. @endif</span>
            </div>
            <div class="row">
                @foreach($this->allinclussions as $inclussion)
                <div class="col-md-4 col-6">
                    <input class="form-check-input" type="checkbox" value="{{$inclussion->id}}" id="{{$inclussion->name}}" wire:model.lazy="inclussions" name="inclussions[]">
                    <label class="form-check-label" for="flexCheckDefault">
                        {{ getLocalizedValue($inclussion) }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>



        <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.bodyOfWater')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;"> @lang('profile.bodyOfWaterMsg')</span>
            </div>
            <input type="text" class="form-control @error('water_name') is-invalid @enderror" id="water_name" wire:model.lazy="water_name" placeholder="@lang('profile.bodyOfWater')" name="water_name">
        </div>

        {{-- this section is to be remove --}}
        <div class="form-group col-md-12 my-2">
            <label class="form-check-label" for="methods_sonstiges">
                @lang('profile.four')
            </label>
            <input type="text" class="form-control" id="additional_information" wire:model.lazy="additional_information" placeholder="@lang('profile.four')" name="additional_information">
        </div>
    </div>
    @endif

    {{-- step 3 --}}
    @if($currentStep == 3)
    <div class="d-flex align-items-center">
        <div class="bg-secondary p-2 rounded d-flex align-items-center"><h4 class="text-white fw-bold me-1">3</h4><small class="text-white">/5</small></h4>
        </div>
        <div class="mx-3 ">
            <h4>@lang('profile.pguidingDetails')</h4>
        </div>
    </div>
    <hr>
    <div class="row">
        {{-- <h3>2. @lang('profile.two')</h3> --}}
        <div class="form-group col-md-6 my-2">
            <div class="my-1">
                <h5>@lang('profile.angelType')?<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Please choose, what the focus of this particular guiding will be. @else Bitte wähle, worauf der Fokus bei diesem Guiding liegt. @endif</span>
            </div>

            <select class="form-control @error('fishing_type') is-invalid @enderror" id="fishing_type" wire:model.lazy="fishing_type" name="fishing_type" required>
                <option class="text-muted" selected>Please Select</option>
                @foreach($allfishingtypes as $index => $fishingtype)
                <option value="{{$fishingtype->id}}">
                    {{ getLocalizedValue($fishingtype) }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6 my-2">
            <div class="my-1">
                <h5>@lang('profile.WhereFrom')?<span style="color:red;">*</span></h5>
                <br>
            </div>
            <div class="form-group">
                <select class="form-control @error('fishing_from') is-invalid @enderror" id="fishing_from" wire:model.lazy="fishing_from" name="fishing_from" required>
                    <option class="text-muted" selected>Please Select</option>
                    @foreach($allfishingfrom as $index => $fishingfrom)
                        <option value="{{$fishingfrom->id}}">
                            {{ getLocalizedValue($fishingfrom) }}
                        </option>
                    @endforeach
                </select>
                @if($aboutBoat)
                    <div class="my-2">
                        <div class="my-1">
                            <h5>@lang('profile.aboutboat')<span style="color:red;">*</span></h5>
                        </div>
                        <input type="text" wire:model.lazy="boat_information" placeholder="@lang('profile.aboutboat')" class="form-control @error('boat_information') is-invalid @enderror">
                    </div>
                    <div class="row">
                        <div class="my-1">
                            <h5>Equipment<span style="color:red;">*</span></h5>
                        </div>
                        @foreach($allfishingequipment as $equipment)
                        <div class="col-md-3 col-6">
                            <input class="form-check-input" type="checkbox" value="{{ $equipment->id }}" id="{{ $equipment->name }}" wire:model.lazy="guidingequipment" name="guidingequipment[]">
                            <label class="form-check-label" for="{{$equipment->name}}">
                               {{ $equipment->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        {{-- <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.gearandeq')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Please choose whether you can provide tackle on a guiding day or not. @else Bitte gib an, ob Du Ruten, Rollen, Köder etc. dabei hast und diese Deinem Gast ggf. oder auch nicht zur Verfügung stellen kannst. @endif</span>
            </div>

            <select class="form-control @error('required_equipment') is-invalid @enderror" id="required_equipment" wire:model.lazy="required_equipment"  name="required_equipment">
                <option class="text-muted" selected>Please Select</option>
                @foreach($allequipmentStatus as $status)
                <option value="{{$status->id}}">
                    {{ getLocalizedValue($status) }}
                </option>
                @endforeach
            </select>
        </div> --}}

        @if($is_needed)
        <div class="form-group col-md-12 my-2" id="needed_equipment">
            <div class="my-1">
                <h5>@lang('profile.neededequipment')<span style="color:red;">*</span></h5>
            </div>
            <input type="text" class="form-control" placeholder="@lang('profile.neededequipment')" wire:model.lazy="needed_equipment" name="needed_equipment">
        </div>
        @endif

        <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.waterType')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Please select the water type in which this guiding takes place. @else Bitte wähle die Gewässerart, an dem dieses Guiding stattfinden wird. @endif</span>
                <span style="color:red; font-size: 12px;">@lang('profile.waterTypeMsg')</span>

            </div>
            @foreach($allwaters as $water)
            <input class="form-check-input @error('water') is-invalid @enderror"  type="checkbox" value="{{$water->id}}" id="{{$water->name}}" wire:model.lazy="water" name="water[]">
                <label class="form-check-label" for="{{$water->name}}">
                    {{ getLocalizedValue($water) }}
                </label>
            @endforeach
        </div>
        <div class="form-group col-md-12 my-2">
            <label class="form-check-label" for="water_sonstiges">
               @lang('profile.otherWaterTypes')
            </label>
            <input class="form-control" type="text" id="water_sonstiges" placeholder="@lang('profile.otherWaterTypes')" wire:model.lazy="water_sonstiges"  name="water_sonstiges">
        </div>

        <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.targetFish')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Choose the target fish for this particular guiding. Multiple selection possible. @else Wähle die Zielfische für dieses Guiding aus. Mehrfachauswahl möglich. @endif</span>
                <span style="color:red; font-size: 12px;">@lang('profile.targetFishMsg')</span>
            </div>
            <div class="row">
                @foreach($alltargets as $target)
                <div class="col-md-3 col-6">
                    <input class="form-check-input @error('targets') is-invalid @enderror" type="checkbox" value="{{$target->id}}"  wire:model.lazy="targets" id="{{$target->name}}" name="targets[]">
                    <label class="form-check-label" for="{{$target->name}}">
                        {{ getLocalizedValue($target) }}
                    </label>

                </div>
                @endforeach
            </div>
        </div>
        <div class="form-group col-md-12 my-2">
            <label class="form-check-label" for="target_fish_sonstiges">
               @lang('profile.otherTargetFish')
            </label>
            <input class="form-control" type="text" id="target_fish_sonstiges" placeholder="@lang('profile.otherTargetFish')" wire:model.lazy="target_fish_sonstiges" name="target_fish_sonstiges">
        </div>
        <div class="form-group col-md-12 my-2">
            <div class="my-1">
                <h5>@lang('profile.techniqueMethod')<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Please select the type of fishing which best describes this particular guiding. @else Wähle bitte die Angelart, die dieses Guiding am besten beschreibt. @endif</span>
                <span style="color:red; font-size: 12px;">*@lang('profile.techniqueMethodMsg')</span>
            </div>
            @foreach($allmethods as $method)
                <input class="form-check-input @error('methods') is-invalid @enderror" type="checkbox" value="{{$method->id}}" wire:model.lazy="methods" id="{{$method->name}}"
                       name="methods[]">
                       <label class="form-check-label" for="{{$method->name}}">
                        {{ getLocalizedValue($method) }}
                       </label>
                <br>
            @endforeach
        </div>
        <div class="form-group col-md-12 my-2">
            <label class="form-check-label" for="methods_sonstiges">
            @lang('profile.otherTechniqueMethod')
            </label>
            <input class="form-control" type="text" id="methods_sonstiges" wire:model.lazy="methods_sonstiges" placeholder="@lang('profile.otherTechniqueMethod')" name="methods_sonstiges">
        </div>
    </div>
    @endif

    @if($currentStep == 4)
    <div class="d-flex align-items-center">
        <div class="my-2">
            <h4>@lang('profile.pdescription')</h4>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="my-1">
            <h5>@lang('profile.three')<span style="color:red;">*</span></h5>
            <span style="color:red; font-size: 12px;">*@lang('profile.threeMsg')</span>
        </div>
        <div class="form-group col-md-12">
                <textarea class="form-control @error('description') is-invalid @enderror" wire:model.lazy="description" name="description" id="description" class="w-100 p-2" rows="10"></textarea>
        </div>
    </div>
    @endif

    @if($currentStep == 5)
    <div class="d-flex align-items-center">
        <div class="bg-secondary p-2 rounded d-flex align-items-center"><h4 class="text-white fw-bold me-1">5</h4><small class="text-white">/5</small></h4>
        </div>
        <div class="mx-3 ">
            <h4>@lang('profile.ppricing')</h4>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="form-group col-md-12 my-2">
            {{-- <div class="my-1">
                <h5>Pricing<span style="color:red;">*</span></h5>
                <span style="color:red; font-size: 12px;">*@lang('profile.fees')</span>
            </div> --}}
            <div class="alert alert-success" role="alert">@lang('profile.feeMsg')</div>
            <ul>
                <li class="text-primary">@lang('profile.feeMsg1').</li>
                <li  class="text-primary">@lang('profile.feeMsg2').</li>
                <li  class="text-primary">@lang('profile.feeMsg3').</li>
            </ul>
        </div>

        <div class="my-2" x-data="{ maxGuests: {{$max_guests}} }">
            <div class="form-group">
                <div class="my-1">
                    <h5>@lang('profile.maxGuest')<span style="color:red;">*</span></h5>
                    <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en') Please provide how many guest at maximum you can guide for this particular guiding. The price application below changes to your selection accordingly in order for you to set prices per number of geusts individually.  @else Gib bitte an, wie viele Gäste Du für dieses Guiding maximal guiden kannst. Entsprechend Deiner Auswahl, kannst Du unten den Preis für die jeweilige Personenanazahl individuell angeben. @endif</span>
                </div>
              <select class="form-control" id="max_guests" wire:model="max_guests" x-model="maxGuests">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <!-- Add more options for different max guests -->
              </select>
            </div>

            <div class="row my-2">
                <div class="form-group col-md-4 my-1"  id="one-person">
                    <label class="text-dark" for="price">@lang('profile.1person')<span style="color:red;">*</span></label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" wire:model.lazy="price" placeholder="@lang('profile.1person')" required>
                </div>

                <div class="form-group col-md-4  my-1"  x-show="maxGuests >= 2" id="two-person">
                    <label class="text-dark" for="price_two_persons">@lang('profile.2person')</label>
                    <input type="number" class="form-control @error('price_two_persons') is-invalid @enderror" id="price_two_persons" wire:model.lazy="price_two_persons" placeholder="@lang('profile.2person')" name="price_two_persons">
                </div>

                <div class="form-group col-md-4  my-1"  x-show="maxGuests >= 3" id="three-person" >
                    <label class="text-dark" for="price_three_persons">@lang('profile.3person')</label>
                    <input type="number" class="form-control @error('price_three_persons') is-invalid @enderror" id="price_three_persons" wire:model.lazy="price_three_persons" placeholder="@lang('profile.3person')" name="price_three_persons">
                </div>

                <div class="form-group col-md-4  my-1"  x-show="maxGuests >= 4" id="four-person" >
                    <label class="text-dark" for="price_four_persons">@lang('profile.4person')</label>
                    <input type="number" class="form-control @error('price_four_persons') is-invalid @enderror" wire:model.lazy="price_four_persons" id="price_four_persons" placeholder="@lang('profile.4person')" name="price_four_persons">
                </div>

                <div class="form-group col-md-4  my-1"  x-show="maxGuests >= 5" id="five-person" >
                    <label class="text-dark" for="price_five_persons">@lang('profile.5person')</label>
                    <input type="number" class="form-control @error('price_five_persons') is-invalid @enderror" wire:model.lazy="price_five_persons" id="price_five_persons" placeholder="@lang('profile.5person')" name="price_five_persons">
                </div>
            </div>


            <!-- Add more x-show blocks for additional fields based on maxGuests -->
          </div>
    </div>

    <div class="row my-1">
        <div class="form-group col-md-12 my-2">
            <div class="d-flex ml-auto align-items-center">
                <div>
                    <h5>@lang('profile.extras')</h5>
                </div>
                <div>
                    <button class="btn btn-sm btn-primary my-2 mx-3" type="button" wire:click="addExtra"><i class="fa fa-plus" aria-hidden="true"></i> Add</button>
                </div>
            </div>
            <span style="color:red; font-size: 12px;">@if(app()->getLocale() == 'en')
                You may have extra offers, that are not included in the guiding price as such. This can be costs for licenses, food & baverage, fishing tackle etc. You can set each extra up and tag it with a price so your potential guest ist able to add the extra to his booking.
                @else
                Du hast zusätzliche Angebote, die zusätzlich zum Gesamtpreis des Guidings anfallen, wie zB. extra Lizensen, Getränke & Verpfelgung, Angelgeräte, etc. Du kannst hier alles extra angeben und den Preis für das jeweilige Extra angeben. Dein Gast kann diese Extras beiseiner Buchung hinzufügen.
                @endif
            </span>
                @foreach ($extras as $key => $extra)
                <div class="row my-2" wire:key="extra-{{ $key }}">
                    <div class="col-md-4 my-1">
                        <input class="form-control" type="text" wire:model.lazy="extras.{{ $key }}.extraName" placeholder="Name" required>
                    </div>
                    <div class="col-md-4 my-1">
                        <input class="form-control" type="number" wire:model.lazy="extras.{{ $key }}.extraPrice" placeholder="Price" required>
                    </div>
                    <div class="col-md-4 my-1">
                        <button class="btn btn-danger" type="button" wire:click="removeExtra({{ $key }})">Remove</button>
                    </div>
                </div>
                @endforeach
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger m-0 p-2" role="alert">
        <div class="d-flex flex-column">
            <small class="text-danger">@lang('message.error-msg')</small>
        </div>
    </div>
    @enderror

    <div class="d-flex my-3">
        @if($currentStep == 1)
        <div></div>
        @endif
        @if($currentStep == 2 || $currentStep == 3 || $currentStep == 4 || $currentStep == 5)
        <button href="#guideform" class="btn btn-primary" wire:loading.attr="disabled" type="button"  wire:click="decreaseStep()">@lang('message.return')</button>
        @endif

        @if($currentStep == 1 || $currentStep == 2 || $currentStep == 3 || $currentStep == 4)
        <button href="#guideform" class="btn btn-success @if($currentStep != 1) mx-2 @endif" wire:loading.attr="disabled" type="button"  wire:click="increaseStep()">@lang('message.further')</button>
        @endif

        @if($currentStep == 5)
            <button class="btn btn-success mx-2" type="button" wire:click="register" wire:loading.attr="disabled">
                <span>Submit</span>
            </button>

        @endif
    </div>





</div>



@push('js_push')
{{-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q') }}&libraries=places,geocoding"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@2"></script>
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
            var country = null;
                for (var i = 0; i < place.address_components.length; i++) {
                    for (var j = 0; j < place.address_components[i].types.length; j++) {
                        if (place.address_components[i].types[j] === 'country') {
                            country = place.address_components[i].long_name;
                            break;
                        }
                    }
                    if (country) {
                        break;
                    }
                }

            Livewire.emit('locationSelected', { lat, lng, location, country });
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

