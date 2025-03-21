@if(isset($models))
    @if($agent->ismobile())
        <div class="new-custom-owl owl-carousel owl-theme">
            @foreach($models as $model)
                <div class="item">
                    <a href="{{ $model->is_newguiding ? route('guidings.show', [$model->id, $model->slug]) : route('guidings.show', [$model->id, $model->slug]) }}">
                        <div class="card" style="min-height:340px;">
                            @if(get_featured_image_link($model))
                                <img src="{{get_featured_image_link($model)}}" class="card-img-top">
                            @else
                                <img src="{{asset('images/placeholder_guide.jpg')}}" class="card-img-top">
                            @endif
                            <div class="card-body">
                            <h5 class="crop-text-2 card-title h6">{{$model->title}}</h5>
                            <small class="crop-text-1 small-text text-muted">{{translate($model->location)}}</small>
                            <small class="fw-bold text-muted">@lang('message.from') <span class="color-primary">{{ $model->getLowestPrice() }}€</span> p.P.</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        @else
        @foreach($models as $model)
        <div class="col-6 col-xs-6 col-md-2 p-1">
            <a href="{{ $model->is_newguiding ? route('guidings.show', [$model->id, $model->slug]) : route('guidings.show', [$model->id, $model->slug]) }}">
                <div class="card h-100 slider-dk">
                    @if(get_featured_image_link($model))
                    <img src="{{get_featured_image_link($model)}}" class="card-img-top">
                    @else
                        <img src="{{asset('images/placeholder_guide.jpg')}}" class="card-img-top">
                    @endif
                    <div class="card-body">
                    <h5 class="crop-text-2 card-title h6">{{translate($model->title)}}</h5>
                    <small class="crop-text-1 small-text text-muted">{{translate($model->location)}}</small>
                    <small class="fw-bold text-muted">@lang('message.from') <span class="color-primary">{{$model->getLowestPrice()}}€</span> p.P.</small>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    @endif
@endif
