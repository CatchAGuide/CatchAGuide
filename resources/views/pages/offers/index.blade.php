@extends('layouts.app-v2')

@php
    $listingTitle = $vm->pageTitle();
@endphp

@section('title', $listingTitle)
@section('header_title', $listingTitle)
@section('header_sub_title', $vm->pageSubtitle())
@section('description', \Illuminate\Support\Str::limit($vm->pageSubtitle(), 155))

@section('content')
<div class="offers-catalog-page" data-offers-page>
    @include('pages.offers.partials.catalog-header')

    <div class="container offers-catalog-page__body offers-page-header__anim" style="--offers-anim-i: 4">
        <x-offers.catalog-listing :vm="$vm" />
    </div>
</div>
@endsection

@section('js_after')
@include('layouts.partials.offers-persons-stepper-script')
@include('components.offers.partials.gallery-script')
<script>
(function () {
    var page = document.querySelector('[data-offers-page]');
    var shell = document.querySelector('[data-offers-header-shell]');
    var hero = document.querySelector('[data-offers-hero]');
    var nav = shell ? shell.querySelector('.cag-site-nav') : null;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (page && !reduceMotion) {
        page.classList.add('has-offers-motion');
        window.requestAnimationFrame(function () {
            page.classList.add('is-offers-ready');
        });
    } else if (page) {
        page.classList.add('is-offers-ready');
    }

    if (nav && hero) {
        var syncNavSolid = function () {
            nav.classList.toggle('is-solid', hero.getBoundingClientRect().bottom <= nav.offsetHeight + 12);
        };
        syncNavSolid();
        window.addEventListener('scroll', syncNavSolid, { passive: true });
        window.addEventListener('resize', syncNavSolid);
    }
})();
</script>
@endsection
