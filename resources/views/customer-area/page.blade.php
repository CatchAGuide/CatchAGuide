{{-- Customer page of /profile/* in the Guide Area design (ProfileController renders it with a
     Livewire component name, so the old routes and their names stay as they are). --}}
@extends('layouts.guide-area')

@section('area-main')
    @livewire($component, $params ?? [])
@endsection
