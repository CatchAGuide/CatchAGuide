<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>@yield('title', 'Bitte den Title setzen!')</title>
    <!-- mobile responsive meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="online catch guide" >
    <meta name="robots" content="INDEX,FOLLOW" >

    <link  rel="icon" type="image/x-icon"  href="{{asset('assets/img/favicon.png')}}">
    <link rel="manifest" href="img/favicon/manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cookieconsent/3.1.1/cookieconsent.min.css"
          integrity="sha512-LQ97camar/lOliT/MqjcQs5kWgy6Qz/cCRzzRzUCfv0fotsCTC9ZHXaPQmJV8Xu/PVALfJZ7BDezl5lW3/qBxg=="
          crossorigin="anonymous"/>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="img/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <!--Fontawesome CDN-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

    <!--LOGIN FORM END-->


    @include('includes.styles')

</head>
<body>
<div class="preloader"></div>
<div class="page-wrapper">

@include('layout.partials.header')

{{--@if(!request()->routeIs('pages.welcome') && !request()->routeIs('login') && !request()->routeIs('register') && !request()->routeIs('password.reset') && !request()->routeIs('password.update') && !request()->routeIs('password.request'))
    @include('layout.partials.breadcrumb')
@endif--}}

@yield('content')


</div><!-- /.page-wrapper -->

@include('layout.partials.footer')
@include('layout.content.cookie-consent')
@include('includes.scripts')


