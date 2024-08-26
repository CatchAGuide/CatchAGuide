
<!-- JQUERY JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<!-- BOOTSTRAP JS -->
<script src="{{ asset('assets/admin/plugins/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

<!-- SPARKLINE JS-->
<script src="{{ asset('assets/admin/js/jquery.sparkline.min.js') }}"></script>

<!-- Sticky js -->
<script src="{{ asset('assets/admin/js/sticky.js') }}"></script>

<!-- CHART-CIRCLE JS-->
<script src="{{ asset('assets/admin/js/circle-progress.min.js') }}"></script>

<!-- PIETY CHART JS-->
<script src="{{ asset('assets/admin/plugins/peitychart/jquery.peity.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/peitychart/peitychart.init.js') }}"></script>

<!-- SIDEBAR JS -->
<script src="{{ asset('assets/admin/plugins/sidebar/sidebar.js') }}"></script>

<!-- Perfect SCROLLBAR JS-->
<script src="{{ asset('assets/admin/plugins/p-scroll/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/p-scroll/pscroll.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/p-scroll/pscroll-1.js') }}"></script>

<!-- INTERNAL CHARTJS CHART JS-->
<script src="{{ asset('assets/admin/plugins/chart/Chart.bundle.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/chart/rounded-barchart.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/chart/utils.js') }}"></script>

<!-- INTERNAL SELECT2 JS -->
<script src="{{ asset('assets/admin/plugins/select2/select2.full.min.js') }}"></script>

<!-- INTERNAL Data tables js-->
<script src="{{ asset('assets/admin/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatable/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/table-data.js') }}"></script>

<!-- INTERNAL APEXCHART JS -->
<script src="{{ asset('assets/admin/js/apexcharts.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/apexchart/irregular-data-series.js') }}"></script>

<!-- C3 CHART JS -->
<script src="{{ asset('assets/admin/plugins/charts-c3/d3.v5.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/charts-c3/c3-chart.js') }}"></script>

<!-- CHART-DONUT JS -->
<script src="{{ asset('assets/admin/js/charts.js') }}"></script>

<!-- INTERNAL Flot JS -->
<script src="{{ asset('assets/admin/plugins/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/flot/jquery.flot.fillbetween.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/flot/chart.flot.sampledata.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/flot/dashboard.sampledata.js') }}"></script>

<!-- INTERNAL Vector js -->
<script src="{{ asset('assets/admin/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>

<!-- SIDE-MENU JS-->
<script src="{{ asset('assets/admin/plugins/sidemenu/sidemenu.js') }}"></script>

<!-- INTERNAL INDEX JS -->
<script src="{{ asset('assets/admin/js/index1.js') }}"></script>

<!-- Color Theme js -->
<script src="{{ asset('assets/admin/js/themeColors.js') }}"></script>

<!-- CUSTOM JS -->
<script src="{{ asset('assets/admin/js/custom.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.ckeditor.com/4.17.1/standard/ckeditor.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

{{-- <script>
    tinymce.init({
        selector: 'textarea#editor',
        menubar: false
    });
</script> --}}

@livewireScripts
@stack('js_push')
@yield('js_after')
@stack('js_after')
