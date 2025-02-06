@extends('layouts.app-v2')

@section('title', __('vacations.title'))
@section('header_title', __('vacations.header_title'))
@section('header_sub_title', __('vacations.header_sub_title'))

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
    .read-more-btn {
        background-color: #E8604C !important;
        color: #fff !important;
        border: 2px solid #E8604C !important;
    }
    </style>
@endsection
@section('content')

<div class="container">
    {{-- <section class="page-header">
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">{{ translate('Fishing Vacations')}}
                        </li>
                        
                    </ul>
                </div>
            </div>
        </div>
    </section> --}}
</div>

<div class="container">
    <section class="toptargetfish">
        <div class="container my-4">
            <div id="page-main-intro" class="section-title my-2">
                <div class="page-main-intro-text">
                    @lang('vacations.introduction')
                </div>
                <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_more')</a></p>
            </div>
            <div class="row">
                @foreach($countries as $country)
                    <div class="col-md-4 my-1">
                        <div class="trending-card">
                            <a href="{{ route('vacations.category', ['country' => $country->slug]) }}"> 
                                <div class="trending-card-wrapper">
                                    <img alt="{{translate($country->name)}}" class="trending-card-background" src="{{asset($country->thumbnail_path ?? 'images/placeholder_guide.jpg')}}">

                                    <div class="trending-card-wrapper-content">
                                        <div class="overlay-wrapper"></div>
                                        <div class="trending-card-main">
                                            <div class="trending-text-wrapper">
                                                <h4 class="trending-title">{{translate($country->name)}}</h4>
                                                <div>
                                                    <img class="mx-2" alt="Flag" width="32" height="32" src="{{asset('flags/'.$country->countrycode.'.svg')}}">
                                                </div>
                                            
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection
@section('js_after')
<script>
    
    $(function(){

        var word_char_count_allowed = 520;
        var page_main_intro = $('.page-main-intro-text');
        var page_main_intro_text = page_main_intro.html();
        var page_main_intro_count = page_main_intro.text().length;
        var ellipsis = "..."; 
        var moreText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_more')</a>';
        var lessText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_less')</a>';

        var visible_text = page_main_intro_text.substring(0, word_char_count_allowed);
        var hidden_text  = page_main_intro_text.substring(word_char_count_allowed);
        console.log(visible_text);
        if (page_main_intro_count >= word_char_count_allowed) {
            console.log(1);
            $('.page-main-intro-text').html(visible_text + '<span class="more-ellipsis">' + ellipsis + '</span><span class="more-text" style="display:none;">' + hidden_text + '</span>');
            //$('.more-text').show();
            $('.see-more').click(function(e) {
                e.preventDefault();
                var textContainer = $(this).prev('.page-main-intro-text'); // Get the content element

                if ($(this).hasClass('less')) {
                    $(this).removeClass('less');
                    $(this).html(moreText);
                    textContainer.find('.more-text').hide();
                    textContainer.find('.more-ellipsis').show();
                } else {
                    $(this).addClass('less');
                    $(this).html(lessText);
                    textContainer.find('.more-text').show();
                    textContainer.find('.more-ellipsis').hide();
                }
            });
        } else {
            console.log(2);
            $('.see-more').hide();
        }

    });

</script>
@endsection