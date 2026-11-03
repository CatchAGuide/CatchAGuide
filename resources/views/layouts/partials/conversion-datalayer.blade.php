{{--
    Conversion data for the thank-you pages (App\Services\Analytics\ConversionDataLayer). Pushed into the
    `datalayer` stack, which tag-manager-head renders before GTM loads, so the Page View conversion tags see it.
--}}
@push('datalayer')
    <script>window.dataLayer = window.dataLayer || []; window.dataLayer.push(@json($conversion));</script>
@endpush
