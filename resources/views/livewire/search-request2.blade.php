<div>
    <form wire:submit.prevent="save" method="POST">
        @if ($page == 1)
            <input name="locale" type="hidden" wire:model="locale" value="{{app()->getLocale()}}">
            <div class="row text-center">
                <div class="col-12">
                    @if(app()->getLocale() == 'en') 
                    <p class="sr-description">Let us find the perfect fishing adventure for you! <br> Use our individual search feature and let us search for fishing trips and fishing vacations across Europe based on your individual wishes and preferences.</p>
                    @else
                    <p class="sr-description">Jetzt das perfekte Angelabenteuer finden lassen! <br> Nutze unsere individuelle Suchfunktion, und lasse nach Angeltouren und Angelurlauben nach Deinen individuellen Wünschen in ganz Europa suchen.</p>
                    @endif
                    <button type="button" class="btn btn-outline-theme my-1" wire:click="next('holidays')">@if(app()->getLocale() == 'en') Fishing Holidays @else Angelurlaub @endif</button>
                    <button type="button" class="btn btn-outline-theme my-1" wire:click="next('guided')">@if(app()->getLocale() == 'en') Guided Fishing Trip @else Angeltour @endif</button>
                </div>
            </div>
        @endif

        @if ($page == 2)

            @if ($fishingType == 'holidays')
                <div class="row text-center">
                    <div class="col-12">

                        <button type="button" class="btn btn-outline-theme my-1" wire:click="next('alone')">@if(app()->getLocale() == 'en') I want to fish alone
                            (accommodation, boat, etc. only) @else Ich will alleine angeln (Nur Unterkunft, Boot, etc.) @endif</button>
                        <button type="button" class="btn btn-outline-theme my-1" wire:click="next('guide')">@if(app()->getLocale() == 'en') fishing vacation with guide (daily
                            guided fishing trips incl.) @else Angelurlaub mit Guide (Täglich geführte Angeltouren inkl.) @endif</button>
                    </div>
                </div>
            @endif

            @if ($fishingType == 'guided')
                <div class="row text-center">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-theme my-1" wire:click="next('halfday')">@if(app()->getLocale() == 'en') Half day trip @else Halbtagestour @endif</button>
                        <button type="button" class="btn btn-outline-theme my-1" wire:click="next('fullday')">@if(app()->getLocale() == 'en') Full day trip @else Ganztagestour @endif</button>
   
                    </div>
                </div>
            @endif

            <button type="button" class="btn btn-outline-theme mt-3" wire:click="prev('0')">@lang('message.return')</button>

                

        @endif

        @if ($page == 3)
            @if ($fishingType == 'holidays')
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label for="">@if(app()->getLocale() == 'en') Days of fishing @else Anzahl der Tage @endif</label>
                            <input type="text" class="form-control  @error('daysOfFishing') is-invalid @enderror" name="" id="" wire:model="daysOfFishing" aria-describedby="helpId" placeholder="">
                        </div>

                        <div class="form-group mb-3">
                            <label for="">@if(app()->getLocale() == 'en') Rental Fishing Boat @else Mietboot (ja/ nein) @endif</label>
                            <select class="form-control @error('rentaboat') is-invalid @enderror" wire:model="rentaboat" name="" id="">
                                <option value="">@if(app()->getLocale() == 'en') Please Select @else Bitte wählen @endif</option>
                                <option value="yes">@if(app()->getLocale() == 'en') Yes @else Ja @endif</option>
                                <option value="no">@if(app()->getLocale() == 'en') No @else Nein @endif</option>
                            </select>
                        </div>                    
                        <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                        <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
                    </div>
                </div>
            @endif

            @if ($fishingType == 'guided')
                <div class="row text-center">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-theme" wire:click="next('shore')">@if(app()->getLocale() == 'en') Shore fishing @else Uferangeln @endif</button>
                        <button type="button" class="btn btn-outline-theme" wire:click="next('boat')">@if(app()->getLocale() == 'en') Boat fishing @else Bootsangeln @endif</button>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-theme mt-3" wire:click="prev('0')">@lang('message.return')</button>

            @endif
        @endif

        @if ($page == 4)
            <div class="row">
                {{-- land coutry --}}
                <div class="form-group col-md-6 my-3">
                    <div>
                        <label class="text-dark fw-bold">@lang('request.country')<span style="color:red;">*</span></label>
                    </div>
                    <div>
                        <input type="text" class="form-control w-100 @error('country') is-invalid @enderror" id="countrySearchPlace"
                            placeholder="@lang('request.country')" autocomplete="off" name="country" wire:model="country" required>
                    </div>
                </div>

                {{-- city region --}}
                <div class="form-group col-md-6 my-3">
                    <div>
                        <label class="text-dark fw-bold">@lang('request.city')<span style="color:red;">*</span></label>
                    </div>
                    <div>
                        <input type="text" class="form-control w-100 @error('city') is-invalid @enderror"
                            id="citySearchPlace" autocomplete="off" name="city"  wire:model="city" placeholder="@lang('request.city')"
                            required>

                    </div>
                </div>
            </div>

            <div class="row">
                {{-- number of guest --}}
                <div class="col-md-6">
                    <div class="row">
                        <div class="my-3 col-md-12">
                            <label class="text-dark fw-bold" for="numberOfGuest">@lang('request.numberOfGuest')<span style="color:red;">*</span></label>
                            <input type="number" min="1" name="numberofguest" wire:model="numberOfGuest" id="numberOfGuest" class="w-100 form-control @error('numberOfGuest') is-invalid @enderror" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="my-3 col-md-6">
                            <label class="text-dark fw-bold" for="numberOfGuest">@lang('request.date-from')<span style="color:red;">*</span></label>
                            <input type="date" id="dateoftour" name="date_of_tour_from" wire:model="date_of_tour_from" class="w-100 form-control @error('date_of_tour_from') is-invalid @enderror" required>
                        </div>
        
                        <div class="my-3 col-md-6">
                            <label class="text-dark fw-bold" for="numberOfGuest">@lang('request.date-to')<span style="color:red;">*</span></label>
                            <input type="date" id="dateoftourto" name="date_of_tour_to" wire:model="date_of_tour_to" class="w-100 form-control @error('date_of_tour_to') is-invalid @enderror">
                        </div>
                    </div>

                </div>
            </div>

            {{-- target fish --}}
            <div class="form-group col-md-12 my-3">
                <div class="my-1">
                    <label class="text-dark fw-bold">@lang('request.target')<span style="color:red;">*</span></label>
                </div>
                <div class="row">
                    @foreach ($alltargets as $target)
                        <div class="col-md-3 col-6">
                            <input class="form-check-input @error('targets') is-invalid @enderror" type="checkbox"
                                value="{{ $target->id }}" wire:model.lazy="targets" id="{{ $target->name }}"
                                name="targets[]">
                            <label class="form-check-label" for="{{ $target->name }}">
                                {{ getLocalizedValue($target) }}
                            </label>

                        </div>
                    @endforeach
                </div>
            </div>

            {{-- fishing method --}}
            <div class="my-3">
                <div class="my-1">
                    <label class="text-dark fw-bold">@lang('request.method')<span style="color:red;">*</span></label>
                </div>
                <div class="row">
                    @foreach ($allmethods as $method)
                        <div class="col-6 col-md-3">
                            <input class="form-check-input @error('methods') is-invalid @enderror" type="checkbox"
                                value="{{ $method->id }}" wire:model.lazy="methods" id="{{ $method->name }}"
                                name="methods[]">
                            <label class="form-check-label" for="{{ $method->name }}">
                                {{ getLocalizedValue($method) }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
            <button type="button" class="btn btn-outline-theme" wire:click="next('0')">@lang('message.further')</button>
        @endif

        @if ($page == 5)
            <div class="row">
                <div class="my-3 col-md-12">
                    <label class="text-dark fw-bold">Name</label>
                    <input type="text" name="name" wire:model="name" class="w-100 form-control @error('name') is-invalid @enderror" required>
                </div>

                <div class="my-3 col-md-6">
                    <label class="text-dark fw-bold">@lang('request.phone')</label>
                    <input type="text" wire:model="phone" name="phone" id="phone" class="w-100 form-control @error('phone') is-invalid @enderror" required>
                </div>

                <div class="my-3 col-md-6">
                    <label class="text-dark fw-bold">@lang('request.email')</label>
                    <input type="email" wire:model="email" name="email" id="email" class="w-100 form-control @error('email') is-invalid @enderror" required>
                </div>
            </div>

            <div class="my-3">
                <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
                <button type="submit" class="btn btn-outline-theme">@lang('request.submit')</button>
            </div>
        @endif

    </form>
</div>

@push('js_push')
    <script>
        $('#target_fish').select2();
    </script>
@endpush
