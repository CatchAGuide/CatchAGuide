<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sash – Bootstrap 5  Admin & Dashboard Template">
    <meta name="author" content="Spruko Technologies Private Limited">
    <meta name="keywords" content="online catch guide" >
    <meta name="robots" content="INDEX,FOLLOW" >

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}" />

    <!-- TITLE -->
    <title>@yield('title', 'Bitte Title setzen') - {{ config('app.name') }}</title>

    @include('admin.auth.layouts.includes.styles')

</head>

<body class="app sidebar-mini ltr">

    @yield('content')

    @include('admin.auth.layouts.includes.scripts')
</body>

</html>
