@foreach($guidings as $guiding)
    @if(!empty($guiding->lat) && !empty($guiding->lng))
        const location{{$guiding->id}} = { lat: {{$guiding->lat}}, lng: {{$guiding->lng}} };

        isDuplicateCoordinate = uniqueCoordinates.some(coordinate => {
            return coordinate.lat === location{{$guiding->id}}.lat && coordinate.lng === location{{$guiding->id}}.lng;
        });

        const isGray{{$guiding->id}} = {{ isset($grayIds) && in_array($guiding->id, $grayIds) ? 'true' : 'false' }};

        let marker{{$guiding->id}};
        let markerOptions{{$guiding->id}};

        if (isDuplicateCoordinate) {
            // Slightly adjust the position to avoid overlapping for duplicate coordinates
            markerOptions{{$guiding->id}} = {
                position: {
                    lat: location{{$guiding->id}}.lat + getRandomOffset(),
                    lng: location{{$guiding->id}}.lng + getRandomOffset(),
                },
                map: map,
            };
        } else {
            // Use the exact location for unique coordinates
            markerOptions{{$guiding->id}} = {
                position: location{{$guiding->id}},
                map: map,
            };
            // Add the unique coordinate to the uniqueCoordinates array
            uniqueCoordinates.push(location{{$guiding->id}});
        }

        if (isGray{{$guiding->id}}) {
            const grayPin{{$guiding->id}} = new PinElement({
                background: '#3C4043', // dark gray for strong contrast
                borderColor: '#111827', // near-black border
                glyph: '•',
                glyphColor: '#ffffff',
                scale: 1.35,
            });
            markerOptions{{$guiding->id}}.content = grayPin{{$guiding->id}}.element;
            markerOptions{{$guiding->id}}.zIndex = 100;
            markerOptions{{$guiding->id}}.collisionBehavior = google.maps.CollisionBehavior.REQUIRED;
        }

        // Track bounds for red (primary) markers so we can focus/zoom the map accordingly
        if (!isGray{{$guiding->id}}) {
            try {
                if (typeof redBounds !== 'undefined' && redBounds && typeof redBounds.extend === 'function') {
                    redBounds.extend(new google.maps.LatLng(location{{$guiding->id}}.lat, location{{$guiding->id}}.lng));
                }
                if (typeof redMarkerCount !== 'undefined') {
                    redMarkerCount += 1;
                }
                if (typeof redCoordinates !== 'undefined' && redCoordinates) {
                    redCoordinates.add(`${location{{$guiding->id}}.lat},${location{{$guiding->id}}.lng}`);
                }
            } catch (e) {
                // no-op; keep rendering markers even if bounds logic fails
            }
        }

        marker{{$guiding->id}} = new google.maps.marker.AdvancedMarkerElement(markerOptions{{$guiding->id}});

        markers.push(marker{{$guiding->id}});

        const infowindow{{$guiding->id}} = new google.maps.InfoWindow({
        content: `
            <div class="card p-0 border-0" style="width: 200px; overflow: hidden;">
                <div class="card-body border-0 p-0">
                    <div class="d-flex">
                        @php
                            $thumbnailSrc = ($guiding->thumbnail_path && file_exists_cached($guiding->thumbnail_path)) 
                                ? asset($guiding->thumbnail_path) 
                                : asset('images/placeholder_guide.jpg');
                        @endphp
                        <img src="{{ $thumbnailSrc }}" alt="{{translate($guiding->title)}}" style="width: 100%; height: 150px; object-fit: cover;">
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