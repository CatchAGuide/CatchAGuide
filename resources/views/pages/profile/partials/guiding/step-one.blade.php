@extends('layouts.app')


@section('profile-content')

<h3>1. @lang('profile.one')</h3>
<div class="row">
    <div class="form-group col-md-12">
        <label for="title">@lang('profile.guidetitle')<span style="color:red;">*</span>
            <span style="color:red; font-size: 12px;">
               @lang('profile.guidetitlemsg')
            </span>
        </label>
        <input type="text" class="form-control" id="title" placeholder="@lang('profile.guidetitle')" wire:model="title" name="title" required>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <label for="searchPlace">@lang('profile.location')<span style="color:red;">*</span></label>
        <input type="text" class="form-control" id="searchPlace" placeholder="@lang('profile.location')"  name="location" required>
        <input type="hidden" id="placeLat" wire:model="lat" name="lat"/>
        <input type="hidden" id="placeLng" wire:model="lng" name="lng"/>
    </div>
</div>
    
@endsection