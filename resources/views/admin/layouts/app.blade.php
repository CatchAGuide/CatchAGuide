<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sash – Bootstrap 5  Admin & Dashboard Template">
    <meta name="author" content="Spruko Technologies Private Limited">
    <meta name="keywords" content="online catch guide" >
    <meta name="robots" content="INDEX,FOLLOW" >
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}" />

    <!-- TITLE -->
    <title>@yield('title', 'Bitte Title setzen') - {{ config('app.name') }}</title>

    @include('admin.layouts.includes.styles')
    @yield('custom_style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css"/>

    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="app sidebar-mini ltr light-mode">

<!-- PAGE -->
<div class="page">
    <div class="page-main">
        @include('admin.layouts.partials.header')

        @include('admin.layouts.partials.sticky-sidebar')

        <!--app-content open-->
        <div class="main-content app-content mt-0">
            @if (\Session::has('success'))
                <div class="alert alert-success">
                    <ul>
                        <li>{!! \Session::get('success') !!}</li>
                    </ul>
                </div>
            @endif
            @if (\Session::has('error'))
                <div class="alert alert-danger">
                    <ul>
                        <li>{!! \Session::get('error') !!}</li>
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
        <!--app-content close-->

    </div>

    @include('admin.layouts.partials.sidebar-right')


    @include('admin.layouts.partials.footer')

</div>

<!-- BACK-TO-TOP -->
<a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

@include('admin.layouts.includes.scripts')

</body>

</html>
