<h4 class="text-danger fw-bold">Step 1<span class="text-muted">/5</span></h4>
<hr>
{{-- step 1--}}
{{-- <h3>1. @lang('profile.one')</h3> --}}
<div class="row">
    <div class="form-group col-md-12 my-2">
        <input  class="form-control" id="fileInput" type="file" name="images[]" accept="image/png, image/jpeg, image/jpg" multiple="multiple" wire:model.lazy="photos">
        <div class="alert alert-primary my-3" role="alert">
            @lang('profile.onemsg')
        </div>
    </div>
    
    <span id="imageCount" class="text-danger"></span>
    @error('selectedPhotos') <span class="text-danger">{{$message}}</span>@enderror

    @if ($selectedPhotos)
        <div class="row my-2">
            @foreach ($selectedPhotos as $index => $photo)
                <div class="col-md-4 col-6 guidings-gallery">
                    <div class="position-relative my-1">
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="img-thumbnail">
                        <button type="button" class="btn btn-danger rounded-0 remove-btn btn-sm" wire:click="removePhoto({{ $index }})">x</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

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
        </div>
        <div>
            <input type="text" class="form-control @error('location') is-invalid @enderror" id="searchPlace" placeholder="@lang('profile.location')" wire:model.lazy="selectedPlace" autocomplete="off" name="location" required>
            <input type="hidden" id="placeLat" wire:model.lazy="lat" name="lat"/>
            <input type="hidden" id="placeLng" wire:model.lazy="lng" name="lng"/>
        </div>
    </div>
</div>

{{-- end step 1--}}