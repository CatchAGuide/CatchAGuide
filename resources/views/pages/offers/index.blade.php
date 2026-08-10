@extends('layouts.app-v2')

@php
    $listingTitle = $vm->pageTitle();
@endphp

@section('title', $listingTitle)
@section('header_title', $listingTitle)
@section('header_sub_title', $vm->pageSubtitle())
@section('description', \Illuminate\Support\Str::limit($vm->pageSubtitle(), 155))

@section('content')
<div class="offers-catalog-page">
    <div class="container">
        <section class="page-header page-header--offers-compact">
            <div class="page-header__bottom breadcrumb-container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">{{ __('offers.breadcrumb') }}</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <div class="container">
        <x-offers.catalog-listing :vm="$vm" />
    </div>
</div>
@endsection

@section('js_after')
@include('components.offers.partials.gallery-script')
@endsection
