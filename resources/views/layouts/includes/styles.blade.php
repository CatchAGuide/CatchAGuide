<link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap/css/bootstrap.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/animate/animate.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/animate/custom-animate.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/fontawesome/css/all.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/jarallax/jarallax.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/jquery-magnific-popup/jquery.magnific-popup.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/nouislider/nouislider.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/nouislider/nouislider.pips.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/odometer/odometer.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/swiper/swiper.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/tevily-icons/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendors/tiny-slider/tiny-slider.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/reey-font/stylesheet.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/owl-carousel/owl.carousel.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/owl-carousel/owl.theme.default.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/twentytwenty/twentytwenty.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/bxslider/jquery.bxslider.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-select/css/bootstrap-select.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/vegas/vegas.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/jquery-ui/jquery-ui.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/timepicker/timePicker.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.css" />

<!-- template styles -->
<link rel="stylesheet" href="{{ asset('assets/css/tevily.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/tevily-responsive.css') }}" />


<!-- webschuppen styles -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}" />

<link rel="stylesheet" type="text/css"
      href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" integrity="sha256-16PDMvytZTH9heHu9KBPjzrFTaoner60bnABykjNiM0=" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme JS files as mentioned below (and change the theme property of the plugin) -->

<!-- optionally if you need translation for your language then include locale file as mentioned below (replace LANG.js with your own locale file) -->
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/css/star-rating.min.css" media="all" rel="stylesheet" type="text/css" />

<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme CSS files as mentioned below (and change the theme property of the plugin) -->
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-svg/theme.css" media="all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css"/>
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />


<style>
    .pagination .page-item .page-link {
        color: var(--thm-primary);
        -webkit-transition: all 0.5s 0.05s ease;
        -moz-transition: all 0.5s 0.05s ease;
        -o-transition: all 0.5s 0.05s ease;
        transition: all 0.5s 0.05s ease;
    }
    .pagination .page-item .page-link:hover {
        background-color: var(--thm-primary);
        border-color: var(--thm-primary);
        color: white;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--thm-primary);
        border-color: var(--thm-primary);
        color: white;
    }
    .main-menu .main-menu__list>li:first-child.current>a::before {
        background-color: inherit;
    }
    .main-menu .main-menu__list>li:first-child>a::before {
        background-color: inherit !important;
        transform: none;
    }
    .js-cookie-consent {
        position: fixed;
        z-index: 99999;
        background-color: #ffffff;
        width: 100%;
        box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
    }
    .cookie-consent__agree {
        cursor: pointer;
        background-color: var(--thm-primary);
        color: white;
        border: 1px solid var(--thm-primary);

        -webkit-transition: all 0.5s 0.05s ease;
        -moz-transition: all 0.5s 0.05s ease;
        -o-transition: all 0.5s 0.05s ease;
        transition: all 0.5s 0.05s ease;
    }
    .cookie-consent__agree:hover {
        color: var(--thm-primary);
        background-color: white;
    }
    .theme-primary{
        color:#fff;
        background-color: #E8604C;
    }
    .color-primary{
        color: #E8604C;
    }
    .btn-outline-danger {
        color: #E8604C;
        border-color: #E8604C;
    }
    .carousel-control-prev-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23000' viewBox='0 0 8 8'%3E%3Cpath d='M5.25 0l-4 4 4 4 1.5-1.5-2.5-2.5 2.5-2.5-1.5-1.5z'/%3E%3C/svg%3E") !important;
    }

    .carousel-control-next-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23000' viewBox='0 0 8 8'%3E%3Cpath d='M2.75 0l-1.5 1.5 2.5 2.5-2.5 2.5 1.5 1.5 4-4-4-4z'/%3E%3C/svg%3E") !important;
    }
    .main-header__top{
        overflow: visible !important;
    }
    .main-menu-wrapper__nav .language-wrapper button{
        background: none !important;
        border:none !important;
    }
    .mobile-nav__top .language-wrapper button{
        background: none !important;
        border:none !important;
    }
    .mobile-nav__top .mobile-nav__social .language-wrapper{
        margin-left:20px;
    }
    .mobile-nav__top .mobile-nav__social .language-wrapper .bootstrap-select>.dropdown-toggle:after{
        color:#fff !important;
    }
    .main-menu-wrapper__nav .language-wrapper .dropdown-toggle::after {
        color:#000000 !important;
    }
    .main-menu-wrapper__nav .language-wrapper ul.dropdown-menu.inner.show{
        display: flex;
        flex-flow: column;
    }
    .bordered-heading {
    border-bottom: 2px solid #E8604C;
    display: inline-block;
    padding-bottom: 5px; /* Adjust as needed */
    }
    .select2-container .select2-search--inline .select2-search__field{
        height: 20px !important;
        color:gray;
        /* padding-left:25px; */
        
    }
    .span.select2.select2-container.select2-container--default.select2-container--below.select2-container--focus{
        /* padding-left:25px !important; */
    }
    .select2-container--default{
        padding-left:25px !important;
    }
    .select2-selection__rendered{
        /* padding-left:25px !important; */
    }
    .select2-container--below{
        padding-left:25px !important;
    }
    
    .select2-selection--multiple{
        border: none !important;
        border-radius: 0 !important;
        /* padding-left:25px !important    ; */
        font-size: 16px;
       
    }
    .select2-icon{
        position: absolute;
        z-index:1;
    }
    .btn-theme-new{
        color:#fff;
        font-weight: bolder;
    }
    .btn-theme-new:hover{
        color:#fff;
    }
            
    @media screen and (max-width: 767px) {
        nav .language-wrapper{
            display: none;
        }
        .magazine-nmb{
            display: none;
        }
        .dropdown.custom:hover .dropdown-menu {
        visibility: visible;
        display:block;
        border-radius:0;
        background-color: #313041;

        }
    }

    @media screen and (min-width: 768px) and (max-width: 1023px) {
        nav .language-wrapper{
          display:none;
        }
    }
 
</style>
@livewireStyles

@yield('css_after')