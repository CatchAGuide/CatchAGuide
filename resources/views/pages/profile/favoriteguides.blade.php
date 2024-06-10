@extends('pages.profile.layouts.profile')

@section('title', ucwords(translate('Lieblingsguidings')))

@section('profile-content')
    <div class="tours-list__right">
        <div class="tours-list__inner">

            @if($wishlist_items && count($wishlist_items) > 0)
                @foreach($wishlist_items as $wishlist_item)
                    <!--Tours List Single-->
                    <a
                            href="{{ route('guidings.show', [$wishlist_item->guiding->id,$wishlist_item->guiding]) }}" style="color: black">
                        <div class="tours-list__single" style="{{$agent->ismobile() ? 'background-color:#faf5ee;  border: 1px solid lightgrey; border-radius: 13px;' : ''}}">
                            <div class="tours-list__img">
                                <img src="{{asset('images/' . $wishlist_item->guiding->thumbnail_path)}}" height="100%" style="width: 100%; height: 350px; object-fit: cover;">
                            </div>
                            <div class="tours-list__content" style="width: 100% !important; font-size: 16px; {{$agent->ismobile() ? 'margin-top:10px; border: 0' : ''}}">
                                <span>{{$wishlist_item->guiding->location}}</span>, <span style="font-size: 12px;">Online seit {{$wishlist_item->guiding->created_at->format('d.m.Y')}}</span><br>
                                <h3 class="tours-list__title">{{$wishlist_item->guiding->title}}</h3>
                                <img src="{{asset('assets/images/icons/fish.png')}}" height="20">
                                {{$wishlist_item->guiding->threeTargets()}}
                                {{$wishlist_item->guiding->target_fish_sonstiges ? $wishlist_item->guiding->target_fish_sonstiges : ""}}
                                <br>
                                <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20">
                                {{$wishlist_item->guiding->threeWaters()}}
                                {{$wishlist_item->guiding->water_sonstiges ? $wishlist_item->guiding->water_sonstiges : ""}}
                                <br>
                                <img src="{{asset('assets/images/icons/fishing-tool.png')}}" height="20"> {{ $wishlist_item->guiding->fishing_type }}<br>
                                <img src="{{asset('assets/images/icons/fishing.png')}}" height="20">
                                {{$wishlist_item->guiding->threeMethods()}}
                                {{$wishlist_item->guiding->methods_sonstiges ? $wishlist_item->guiding->methods_sonstiges : ""}}
                                <br>
                                <img src="{{asset('assets/images/icons/fishing-man.png')}}" height="20">
                                {{ $wishlist_item->guiding->fishing_from }}<br>
                                <p class="tours-list__rate" style="text-align: right;"><span>{{$wishlist_item->guiding->price}} €</span> / pro Person</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="text-center">
                    <h4>{{translate('Noch hast du keine Lieblingsguidings!')}} 💔</h4>
                    <b>{{translate('Lass uns das schleunigst ändern')}}</b><br/><br/>
                    <a href="{{ route('guidings.index') }}" class="thm-btn">{{translate('zu den Guidings')}}</a>
                </div>
            @endif
        </div>
    </div>
@endsection
