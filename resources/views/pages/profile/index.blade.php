@extends('pages.profile.layouts.profile')
@section('title', __('profile.profile'))
@section('profile-content')

    <div class="container">
        <section class="page-header">
            <div class="page-header__bottom">
                <div class="container">
                    <div class="page-header__bottom-inner">
                        <ul class="thm-breadcrumb list-unstyled">
                            <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                            <li><span>&#183;</span></li>
                            <li class="active">
                                {{ translate('Profile') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if(!$agent->ismobile())
        <div class="profile-options row gx-5">
            <div class="col-md-3 profile-option-items">
                <a href="{{ route('profile.settings') }}">
                    <div class="box">
                        <i class="fa fa-2x fa-user"></i>
                        <span class="box-title">@lang('profile.ideas')</span>
                    </div>
                </a>
            </div>
            <div class="col-md-3 profile-option-items">
                <a href="{{ route('profile.bookings') }}">
                    <div class="box">
                        <i class="fa fa-2x fa-shopping-bag"></i>
                        <span class="box-title">@lang('profile.bookBy')</span>
                    </div>
                </a>
            </div>
            <!-- <div class="col-md-3 profile-option-items">
                <a href="{{ route('profile.favoriteguides') }}">
                    <div class="box">
                        <i class="fa fa-2x fa-heart"></i>
                        <span class="box-title">@lang('profile.favorite')</span>
                    </div>
                </a>
            </div> -->
            <!-- <div class="col-md-3 profile-option-items">
                <a href="{{ route('chat') }}">
                    <div class="box">
                        <i class="fa fa-2x fa-paper-plane"></i>
                        <span class="box-title">@lang('profile.mess') ({{Auth::user()->countunreadmessages()}})</span>
                    </div>
                </a>
            </div> -->
            @if(Auth::user()->is_guide)
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.newguiding') }}">
                        <div class="box">
                            <i class="fa fa-2x fa-plus-circle"></i>
                            <span class="box-title">@lang('profile.creategiud')</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.myguidings') }}">
                        <div class="box">
                            <i class="fa fa-2x fa-box-open"></i>
                            <span class="box-title">@lang('profile.myGuides')</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.guidebookings') }}">
                        <div class="box">
                            <i class="fa fa-2x fa-box-tissue"></i>
                            <span class="box-title">@lang('profile.bookedWithMe')</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.calendar') }}">
                        <div class="box">
                            <i class="fa fa-2x fa-calendar-alt"></i>
                            <span class="box-title">@lang('profile.calendar')</span>
                        </div>
                    </a>
                </div>
            @else
                <div class="col-md-3 profile-option-items">
                    <a href="{{route('profile.becomeguide')}}">
                        <div class="box">
                            <i class="fa fa-2x fa-envelope"></i>
                            <span class="box-title">@lang('profile.verify')</span>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="row gx-5 profile-options">
            <div class="col-md-3 profile-option-items">
                <a href="{{ route('profile.settings') }}">
                    <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                        <i class="fa fa-user"></i>
                        <span class="box-title">@lang('profile.profile')</span>
                    </button>
                </a>
            </div>
            <div class="col-md-3 profile-option-items">
                <a href="{{ route('profile.bookings') }}">
                    <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                        <i class="fa fa-shopping-bag"></i>
                        <span class="box-title">@lang('profile.bookBy')</span>
                    </button>
                </a>
            </div>
            <!-- <div class="col-md-3 profile-option-items">
                <a href="{{ route('profile.favoriteguides') }}">
                    <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                        <i class="fa fa-heart"></i>
                        <span class="box-title">@lang('profile.favorite')</span>
                    </button>
                </a>
            </div> -->
            {{-- <div class="col-md-3 profile-option-items">
                <a href="{{ route('chat') }}">
                    <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                        <i class="fa fa-2x fa-paper-plane"></i>
                        <span class="box-title">@lang('profile.mess') ({{Auth::user()->countunreadmessages()}})</span>
                    </button>
                </a>
            </div> --}}
            @if(Auth::user()->is_guide)
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.newguiding') }}">
                        <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                            <i class="fa fa-plus-circle"></i>
                            <span class="box-title">@lang('profile.creategiud')</span>
                        </button>
                    </a>
                </div>
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.myguidings') }}">
                        <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                            <i class="fa fa-box-open"></i>
                            <span class="box-title">@lang('profile.myGuides')</span>
                        </button>
                    </a>
                </div>
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.guidebookings') }}">
                        <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                            <i class="fa fa-box-tissue"></i>
                            <span class="box-title">@lang('profile.bookedWithMe')</span>
                        </button>
                    </a>
                </div>
                <div class="col-md-3 profile-option-items">
                    <a href="{{ route('profile.calendar') }}">
                        <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                            <i class="fa fa-calendar-alt"></i>
                            <span class="box-title">@lang('profile.calendar')</span>
                        </button>
                    </a>
                </div>
            @else
                <div class="col-md-3 profile-option-items">
                    <a href="{{route('profile.becomeguide')}}">
                        <button style="width: 100%; text-align: justify;" class="btn  btn-block">
                            <i class="fa fa-envelope"></i>
                            <span class="box-title">@lang('profile.verify')</span>
                        </button>
                    </a>
                </div>
            @endif
        </div>

    @endif
@endsection
