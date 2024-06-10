@extends('layouts.app')


@section('profile-content')
<div class="row">
    <div class="form-group col-md-12">
        <h4 style="margin-bottom: 15px;">@lang('profile.designfor')<span style="color:red;">*</span></h4>

        <input class="form-check-input" type="checkbox" value=1 id="recommended_for_anfaenger" wire:model="recommended_for_anfaenger" name="recommended_for_anfaenger">

        <label class="form-check-label" for="flexCheckDefault">
            @lang('profile.begginer')
        </label>
        <br>

        <input class="form-check-input" type="checkbox" value=1 id="recommended_for_fortgeschrittene" wire:model="recommended_for_fortgeschrittene" name="recommended_for_fortgeschrittene">
        <label class="form-check-label" for="flexCheckDefault">
            @lang('profile.advanced')
        </label>

        <br>
        <input class="form-check-input" type="checkbox" value=1 id="recommended_for_profis" wire:model="recommended_for_profis" name="recommended_for_profis">
        <label class="form-check-label" for="flexCheckDefault">
            @lang('profile.professionals')
        </label>
    </div>

    <div class="form-group col-md-12">
        <label for="duration">@lang('profile.duration')<span style="color:red;">*</span>
            <span style="color:red; font-size: 12px;">
               @lang('profile.durationmsg')
            </span>
        </label>
        <input type="number" class="form-control" id="duration" name="duration" wire:model="duration"  placeholder="@lang('profile.duration')" required>
    </div>
</div>

<div class="row">
    <div class="form-group col-md-12">
        <label for="required_special_license">@lang('profile.specificguestcard')<span style="color:red;">*</span>
            <br>
            <span style="color:red; font-size: 12px;">
               @lang('profile.specificguestcardmsg')
            </span>
        </label>
        <select class="form-control" id="special_license_needed" wire:model="special_license_needed"  name="special_license_needed" required>
            <option value="Nein">@lang('profile.no')</option>
            <option value="Ja">@lang('profile.yes')</option>
        </select>
    </div>
    <div class="form-group col-md-12" id="special_lizence" hidden>
        <label for="required_special_license">Bitte gib die Gastkarte oder den Gewässerschein an der benötigt wird*</label>
        <input type="text" class="form-control" id="required_special_license"
               placeholder="Gewässerkarte/Gewässerschein" wire:model="required_special_license" name="required_special_license">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-12 mb-4 mt-3">
        <label for="water_name">@lang('profile.bodyOfWater')<span style="color:red;">*</span><span style="color:red; font-size: 12px;"> @lang('profile.bodyOfWaterMsg')</span></label>
        <input type="text" class="form-control" id="water_name" wire:model="water_name" placeholder="@lang('profile.bodyOfWater')" name="water_name">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-12">
        <label for="provided_equipment">@lang('profile.meetingPoint')<span style="color:red;">*</span></label>
        <input type="text" class="form-control" id="meeting_point" wire:model="meeting_point" placeholder="@lang('profile.meetingPoint')"
               name="meeting_point">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-12">
        <label for="additional_information">@lang('profile.four')</label>
        <input type="text" class="form-control" id="additional_information" wire:model="additional_information" placeholder="@lang('profile.four')"
               name="additional_information">
    </div>
</div>
@endsection