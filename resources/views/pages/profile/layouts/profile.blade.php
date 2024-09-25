@extends('layouts.app-v2-1')

@section('css_after')
    <style>
        .box {
            border: 1px solid lightgrey;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            padding: 25% 0;
            cursor: pointer;

            -webkit-transition: all 0.4s 0s ease-in-out;
            -moz-transition: all 0.4s 0s ease-in-out;
            -o-transition: all 0.4s 0s ease-in-out;
            transition: all 0.4s 0s ease-in-out;
        }

        .box:hover {
            border-color: var(--thm-primary);
        }

        .box > i {
            font-size: 1.6em;
        }

        .box-title {
            margin-top: 5px;
        }

        .accordion-button {
            color: gray !important;
        }

        .accordion-button:not(.collapsed) {
            background-color: inherit;
            color: inherit;
        }

        .accordion-button:focus {
            z-index: 3;
            border-color: inherit;
            outline: 0;
            box-shadow: inherit;
        }

        .fa-chevron-right {
            font-size: 10px;
        }

        .accordion-body a:hover {
            color: var(--thm-primary);
        }

        .col-4 a:hover {
            color: var(--thm-primary);
        }
    </style>
@stop

@section('content')
    <!--Page Header Start-->
    <!-- <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg"
                 style="background-image: url({{asset('assets/images/allguidings.jpg')}}); ">
            </div>
            <div class="page-header-bg-overly"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h2>@yield('title')</h2>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">

                        <li><a href="{{route('welcome')}}">Home</a></li>
                        <li><span>&#183;</span></li>
                        @unless(Request::routeIs('profile.index'))
                            <li><a href="{{route('profile.index')}}">@lang('profile.profile')</a></li>
                            <li><span>&#183;</span></li>
                        @endunless
                        @if(Request::routeIs('guidings.edit'))
                            <li><a href="{{route('profile.myguidings')}}">{{translate('Meine Guidings')}}</a></li>
                            <li><span>&#183;</span></li>
                        @endif

                        <li class="active">@yield('title')</li>


                    </ul>
                </div>
            </div>
        </div>
    </section> -->
    <!--Page Header End-->
    <div class="container" style=" margin-bottom: 20px;">
        <div class="row mt-3">
            <div class="{{!$agent->isMobile() ? 'col-12' : 'col-12'}}">
                    <h2>@yield('title')</h2>
                @yield('profile-content')
            </div>
        </div>
    </div>

@endsection

@section('js_after')
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"
            integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous"
            async></script>
@endsection
