
<!-- BOOTSTRAP CSS -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<!-- STYLE CSS -->
<link href="{{ asset('assets/admin/css/style.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/dark-style.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/css/transparent-style.css') }}" rel="stylesheet">
<link href="{{ asset('assets/admin/css/skin-modes.css') }}" rel="stylesheet" />

<!--C3 CHARTS CSS -->
<link href="{{ asset('assets/admin/plugins/charts-c3/c3-chart.css') }}" rel="stylesheet" />

<!-- P-scroll bar css-->
<link href="{{ asset('assets/admin/plugins/p-scroll/perfect-scrollbar.css') }}" rel="stylesheet" />

<!--- FONT-ICONS CSS -->
<link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet" />

<!-- INTERNAL Jvectormap css -->
<link href="{{ asset('assets/admin/plugins/jvectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />

<!-- SIDEBAR CSS -->
<link href="{{ asset('assets/admin/plugins/sidebar/sidebar.css') }}" rel="stylesheet">

<!-- SELECT2 CSS -->
<link href="{{ asset('assets/admin/plugins/select2/select2.min.css') }}" rel="stylesheet" />

<!-- INTERNAL Data table css -->
<link href="{{ asset('assets/admin/plugins/datatable/css/dataTables.bootstrap5.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/admin/plugins/datatable/responsive.bootstrap5.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" />

<!-- COLOR SKIN CSS -->
<link id="theme" rel="stylesheet" type="text/css" media="all" href="{{ asset('assets/admin/colors/color1.css') }}" />

@livewireStyles

@yield('css_after')
