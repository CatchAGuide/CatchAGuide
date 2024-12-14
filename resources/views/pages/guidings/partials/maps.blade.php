@foreach($guidings as $guiding)
@if(!empty($guiding->lat) && !empty($guiding->lng))
const location{{$guiding->id}} = { lat: {{$guiding->lat}}, lng: {{$guiding->lng}} };

isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
    return coordinate.lat === location{{$guiding->id}}.lat && coordinate.lng === location{{$guiding->id}}.lng;
});

let marker{{$guiding->id}};

if (isDuplicateCoordinate) {
    // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
    const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
    marker{{$guiding->id}} = new google.maps.marker.AdvancedMarkerElement({
        position: {
            lat: location{{$guiding->id}}.lat + getRandomOffset(),
            lng: location{{$guiding->id}}.lng + getRandomOffset(),
        },
        map: map,
    });
} else {
    // If the coordinate is unique, create the marker as usual
    marker{{$guiding->id}} = new google.maps.marker.AdvancedMarkerElement({
        position: location{{$guiding->id}},
        map: map,
    });
    // Add the unique coordinate to the uniqueCoordinates array
    uniqueCoordinates.push(location{{$guiding->id}});
}

markers.push(marker{{$guiding->id}});

const infowindow{{$guiding->id}} = new google.maps.InfoWindow({
content: `
    <div class="card p-0 border-0" style="width: 200px;">
        <div class="card-body border-0 p-0">
            <a class="text-decoration-none" href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}"><h5 class="card-title" style="font-size: 14px;">{{translate($guiding->title)}}</h5></a>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                    @php
                    $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();
                    @endphp
                    
                    @if(!empty($guidingTargets))
                        {{ implode(', ', $guidingTargets) }}
                    @else
                    {{ $guiding->threeTargets()}}
                    {{$guiding->target_fish_sonstiges ? " & " . $guiding->target_fish_sonstiges : ""}}
                    @endif
                </p>
                </div>
            </div>
            <div class="d-flex align-items-center my-1">
                <div>
                    <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                </div>
                <div class="mx-1">         
                <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                    @php
                    $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();
                    @endphp
                    
                    @if(!empty($guidingWaters))
                        {{ implode(', ', $guidingWaters) }}
                    @else
                    {{ $guiding->threeWaters() }}
                    {{$guiding->water_sonstiges ? " & " . $guiding->water_sonstiges : ""}}
                    @endif
                </p>
                </div>
            </div>
            <div class="text-center mt-2">
                <a class="theme-primary text-center my-2" href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}" style="padding:3px 7px;">@lang('message.from') {{$guiding->price}}€</a>
            </div>

        </div>
    </div>
`
});


infowindows.push(infowindow{{$guiding->id}});

marker{{$guiding->id}}.addListener("click", () => {
    infowindows.forEach((infowindow) => {
        infowindow.close();
    });
    infowindow{{$guiding->id}}.open(map, marker{{$guiding->id}});
});
@endif
@endforeach