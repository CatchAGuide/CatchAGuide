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
            <div class="card p-0 border-0" style="width: 200px; overflow: hidden;">
                <div class="card-body border-0 p-0">
                    <div class="d-flex">
                        @php
                            // if (file_exists(public_path($guiding->thumbnail_path))) {
                                $thumbnailPath = asset($guiding->thumbnail_path);
                            // } else {
                            //     $thumbnailPath = asset('images/placeholder_guide.jpg');
                            // }
                        @endphp
                        <img src="{{$thumbnailPath}}" alt="{{translate($guiding->title)}}" style="width: 100%; height: 150px; object-fit: cover;">
                    </div>
                    <div class="p-2">
                        <a class="text-decoration-none" href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}">
                            <h5 class="card-title mb-1" style="font-size: 14px; font-weight: bold; color: #333;">{{translate($guiding->title)}}</h5>
                        </a>
                        <div class="text-muted small">{{$guiding->location}}</div>
                        <div class="mt-2">
                            <span class="fw-bold">ab {{$guiding->getLowestPrice()}}€</span> p.P.
                        </div>
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