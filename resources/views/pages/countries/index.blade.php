@extends('layouts.app-v2')


    @section('title', __('destination.title'))
    @section('header_title', __('destination.header_title'))
    @section('header_sub_title', __('destination.header_sub_title'))

@section('custom_style')
    <style>
         .trending-card{
        position: relative;
        height:240px;
    }
    .trending-card .trending-card-wrapper {
        border-radius: 3px;
        width: 100%;
        height: 100%;
        margin:10px 0px;
        position: relative;
        background: #000;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content {
        position: absolute;
        bottom: 0;
        width: 100%;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .overlay-wrapper {
        display: block;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(180deg,rgba(0,0,0,.2) 0,rgba(0,0,0,.4) 100%);
        z-index: 2;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main {
        width: 100%;
        position: relative;
        bottom: 0;
        left: 0;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main .trending-text-wrapper {
        display: flex;
        color:#fff;
        padding:20px 20px;
    }
    .trending-card .trending-card-wrapper .trending-card-wrapper-content .trending-card-main .trending-text-wrapper .trending-title {
        font-weight: bold;
        color:#fff;
    }
    .trending-card .trending-card-wrapper .trending-card-background {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        padding: 0;
        opacity: .6;
    }
    </style>
@endsection
@section('content')
<div class="container">
    <section class="toptargetfish">
        <div class="container my-4">
            <div class="section-title my-2">
                <p>@lang('destination.introduction')</p>
            </div>
            <div class="row">
                <div class="col-md-4 my-1">
                    <div class="trending-card">
                        <a href="/guidings?country=Germany"> 
                            <div class="trending-card-wrapper">
                                <img alt="Deutschland" class="trending-card-background" src="{{asset('assets/2024/germany/deutschland4.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Germany</h4>
                                            <div>
                                                <img class="mx-2" alt="Key West" width="32" height="32" src="{{asset('flags/de.svg')}}">
                                            </div>
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 my-1">
                    <div class="trending-card">
                        <a href="/guidings?country=Netherlands">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/netherlands/holland1.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Netherlands</h4>
                                            <div>
                                                <img class="mx-2" alt="Key West" width="32" height="32" src="{{asset('flags/nl.svg')}}">
                                            </div>
                                      
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 my-1">
                    <div class="trending-card">
                        <a href="/guidings?country=Denmark">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/denmark/denmark.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Denmark</h4>
                                            <div>
                                                <img class="mx-2" alt="Key West" width="32" height="32" src="{{asset('flags/dk.svg')}}">
                                            </div>
                    
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 my-1">
                    <div class="trending-card">
                        <a href="/guidings?country=Sweden"> 
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/sweden/schweden5.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Sweden</h4>
                                            <div>
                                                <img class="mx-2" alt="Key West" width="32" height="32" src="{{asset('flags/se.svg')}}">
                                            </div>  
                                 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 my-1">
                    <div class="trending-card">
                        <a href="/guidings?country=Spain">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/spain/spain.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Spain</h4>
                                            <div>
                                                <img class="mx-2" alt="Key West" width="32" height="32" src="{{asset('flags/es.svg')}}">
                                            </div>
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 my-1">
                    <div class="trending-card">
                        <a href="/guidings?country=Portugal">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/portugal/portugal.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Portugal</h4>
                                            <div>
                                                <img class="mx-2" alt="Key West" width="32" height="32" src="{{asset('flags/pt.svg')}}">
                                            </div>
                 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 my-1">
                    <div class="trending-card">
                        <a href="/guidings?country=Croatia">
                            <div class="trending-card-wrapper">
                                <img alt="Key West" class="trending-card-background" src="{{asset('assets/2024/croatia/croatia.webp')}}">
                                <div class="trending-card-wrapper-content">
                                    <div class="overlay-wrapper"></div>
                                    <div class="trending-card-main">
                                        <div class="trending-text-wrapper">
                                            <h4 class="trending-title">Croatia</h4>
                                            <div>
                                                <img class="mx-2" alt="Key West" width="32" height="32" src="{{asset('flags/hr.svg')}}">
                                            </div>
                                   
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection