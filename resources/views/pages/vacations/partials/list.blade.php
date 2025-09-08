@foreach($vacations as $vacation)
<div class="row m-0 mb-2 guiding-list-item">
	<div class="tours-list__right col-md-12">
		<div class="row p-2 border shadow-sm bg-white rounded">
			<div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
				<div id="carouselExampleControls-{{$vacation->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
					<div class="carousel-inner">
						@if(count(get_galleries_image_link($vacation, 1)))
							@foreach(get_galleries_image_link($vacation, 1) as $index => $gallery_image_link)
								<div class="carousel-item @if($index == 0) active @endif">
									<img  class="carousel-image" src="{{asset($gallery_image_link)}}">
								</div>
							@endforeach
						@endif
					</div>
					@if(count(get_galleries_image_link($vacation, 1)) > 1)
						<button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$vacation->id}}" data-bs-slide="prev">
							<span class="carousel-control-prev-icon" aria-hidden="true"></span>
							<span class="visually-hidden">Previous</span>
						</button>
						<button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$vacation->id}}" data-bs-slide="next">
							<span class="carousel-control-next-icon" aria-hidden="true"></span>
							<span class="visually-hidden">Next</span>
						</button>
					@endif
				</div>
			</div>
			<div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 p-md-3 mt-md-1">
			<a href="{{ route('vacations.show', [$vacation->id, $vacation->slug]) }}" 
			   onclick="event.preventDefault(); 
						document.getElementById('store-destination-{{ $vacation->id }}').submit();">
				<div class="guidings-item">
					<div class="guidings-item-title">
						@if(!$agent->ismobile())
						<h5 class="fw-bolder text-truncate">{{translate($vacation->title)}}</h5>
						@endif
						@if($agent->ismobile())
							<h5 class="fw-bolder text-truncate">{{ \Str::limit(translate($vacation->title), 65) }}</h5>
						@endif
						<span class=""><i class="fas fa-map-marker-alt me-2"></i>{{ $vacation->location }} </span>
					</div>
					<div class="inclusions-price">
					<div class="guiding-item-price">
						<h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$vacation->getLowestPrice()}}€ p.P.</span></h5>
						<div class="d-none d-flex flex-column mt-4">
						</div>
					</div>
				</div>
				</div>
				<div class="vacations-item-row">
					<div class="vacations-item-row-top">
					</div>
						<div class="vacations-info-container"> 
							<span class="fw-bold">{{translate('Boat Available')}}:</span>
							<span class="text-regular">{{ count($vacation->boats) || $vacation->has_boat > 0 ? translate('Available') : translate('Unavailable') }}</span>
						</div>
						<div class="vacations-info-container"> 
							<span class="fw-bold">{{translate('Distance to the water')}}:</span>
							<div class="">
								{{ $vacation->water_distance }}
							</div>
						</div>
					<div class="vacations-info-container"> 
						<span class="fw-bold">{{translate('Target Fish')}}:</span>
						<div class="d-flex">
							@php
								$target_fish = $vacation->target_fish;
							@endphp
							<ul class="list-unstyled mb-0 d-flex">
								{{ translate(\Str::limit(implode(', ', $target_fish), limit:50 )) }}
							</ul>
						</div>
					</div>
				</div>
			</a>
			<form id="store-destination-{{ $vacation->id }}" 
				  action="{{ route('vacations.show', [$vacation->id, $vacation->slug]) }}" 
				  method="GET" style="display: none;">
				@php
					session(['vacation_destination_id' => $row_data->id]);
				@endphp
			</form>
		</div>
		</div>
	</div>
</div>
@endforeach
{!! $vacations->links('vendor.pagination.default') !!}









