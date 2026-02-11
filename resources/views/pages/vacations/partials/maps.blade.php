@foreach($vacations as $vacation)
    @if(!empty($vacation->latitude) && !empty($vacation->longitude))
    const location{{$vacation->id}} = { lat: {{$vacation->latitude}}, lng: {{$vacation->longitude}} };

    isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
        return coordinate.lat === location{{$vacation->id}}.lat && coordinate.lng === location{{$vacation->id}}.lng;
    });

    let marker{{$vacation->id}};

    if (isDuplicateCoordinate) {
        // If the coordinate is a duplicate, slightly adjust the position to avoid overlapping
        const randomOffset = Math.random() / 1000; // Adjust this value based on your requirement
        marker{{$vacation->id}} = new google.maps.marker.AdvancedMarkerElement({
            position: {
                lat: location{{$vacation->id}}.lat + getRandomOffset(),
                lng: location{{$vacation->id}}.lng + getRandomOffset(),
            },
            map: map,
        });
    } else {
        // If the coordinate is unique, create the marker as usual
        marker{{$vacation->id}} = new google.maps.marker.AdvancedMarkerElement({
            position: location{{$vacation->id}},
            map: map,
        });
        // Add the unique coordinate to the uniqueCoordinates array
        uniqueCoordinates.push(location{{$vacation->id}});
    }

    markers.push(marker{{$vacation->id}});

    const infowindow{{$vacation->id}} = new google.maps.InfoWindow({
    content: `
        <div class="card p-0 border-0" style="width: 200px; overflow: hidden;">
            <div class="card-body border-0 p-0">
                <div class="d-flex">
                    
                    @php
                        $gallery = get_galleries_image_link($vacation, 1);
                    @endphp
                    <img src="{{$gallery[0]}}" alt="{{translate($vacation->title)}}" style="width: 100%; height: 150px; object-fit: cover;">
                </div>
                <div class="p-2">
                    <a class="text-decoration-none" href="{{route('vacations.show',[$vacation->id,$vacation->slug])}}">
                        <h5 class="card-title mb-1" style="font-size: 14px; font-weight: bold; color: #333;">{{translate($vacation->title)}}</h5>
                    </a>
                    <div class="text-muted small">{{$vacation->location}}</div>
                    <div class="mt-2">
                        <span class="fw-bold">ab {{$vacation->getLowestPrice()}}€</span> p.P.
                    </div>
                </div>
            </div>
        </div>
    `
    });


    infowindows.push(infowindow{{$vacation->id}});

    marker{{$vacation->id}}.addListener("gmp-click", () => {
        infowindows.forEach((infowindow) => {
            infowindow.close();
        });
        infowindow{{$vacation->id}}.open(map, marker{{$vacation->id}});
    });
    @endif
@endforeach