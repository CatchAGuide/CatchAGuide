@extends('admin.layouts.app')

@section('title', __('trips.show'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title">
                    <h1>{{ __('trips.show') }}</h1>
                    <p class="text-muted">{{ $trip->title ?: 'Untitled Trip' }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('trips.back_to_list') }}
                    </a>
                    <a href="{{ route('admin.trips.edit', $trip->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> {{ __('trips.edit') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Trip Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Basic Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Title:</strong></td>
                                            <td>{{ $trip->title ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Location:</strong></td>
                                            <td>{{ $trip->location ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $trip->status === 'active' ? 'success' : ($trip->status === 'draft' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($trip->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $trip->user->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created At:</strong></td>
                                            <td>{{ $trip->created_at?->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated At:</strong></td>
                                            <td>{{ $trip->updated_at?->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Location Details</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Country:</strong></td>
                                            <td>{{ $trip->country ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>City:</strong></td>
                                            <td>{{ $trip->city ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Region:</strong></td>
                                            <td>{{ $trip->region ?: 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Coordinates:</strong></td>
                                            <td>
                                                @if($trip->latitude && $trip->longitude)
                                                    {{ $trip->latitude }}, {{ $trip->longitude }}
                                                @else
                                                    Not set
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Descriptions</h5>
                                    <h6>Main Description</h6>
                                    <div class="text-muted trip-description-content">{!! $trip->description ?: '<span class="text-muted">No description provided</span>' !!}</div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Fishing Details</h5>
                                    <p><strong>Target Species:</strong>
                                        @php
                                            $targetSpecies = collect($trip->getTargetSpeciesNames())->pluck('name')->filter()->values()->all();
                                        @endphp
                                        @if(count($targetSpecies))
                                            {{ implode(', ', $targetSpecies) }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                    <p><strong>Fishing Methods:</strong>
                                        @php
                                            $fishingMethods = collect($trip->getFishingMethodNames())->pluck('name')->filter()->values()->all();
                                        @endphp
                                        @if(count($fishingMethods))
                                            {{ implode(', ', $fishingMethods) }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                    <p><strong>Water Types:</strong>
                                        @php
                                            $waterTypes = collect($trip->getWaterTypeNames())->pluck('name')->filter()->values()->all();
                                        @endphp
                                        @if(count($waterTypes))
                                            {{ implode(', ', $waterTypes) }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                    <p><strong>Skill Level:</strong>
                                        {{ $trip->skill_level ? ucfirst(str_replace('_', ' ', $trip->skill_level)) : 'Not set' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Duration & Group Size</h5>
                                    <p><strong>Duration:</strong>
                                        @if($trip->duration_nights || $trip->duration_days)
                                            {{ $trip->duration_nights ? $trip->duration_nights . ' nights' : '' }}
                                            @if($trip->duration_nights && $trip->duration_days) /
                                            @endif
                                            {{ $trip->duration_days ? $trip->duration_days . ' days' : '' }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                    <p><strong>Group Size:</strong>
                                        @if($trip->group_size_min || $trip->group_size_max)
                                            @if($trip->group_size_min)
                                                {{ $trip->group_size_min }}–
                                            @endif
                                            {{ $trip->group_size_max }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                    <p><strong>Meeting Point:</strong>
                                        {{ $trip->meeting_point ?: 'Not set' }}
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Trip Schedule</h5>
                                    @if(is_array($trip->trip_schedule) && count($trip->trip_schedule))
                                        <ul class="list-group">
                                            @foreach($trip->trip_schedule as $day)
                                                <li class="list-group-item">
                                                    <strong>{{ $day['day_label'] ?? 'Day' }}:</strong>
                                                    <span class="text-muted">{{ $day['description'] ?? '' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted">No schedule provided</p>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Included & Excluded</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Included</h6>
                                            @if(is_array($trip->included) && count($trip->included))
                                                <ul class="list-unstyled">
                                                    @foreach($trip->included as $item)
                                                        @php
                                                            $label = is_array($item) && isset($item['name']) ? $item['name'] : $item;
                                                        @endphp
                                                        <li>• {{ $label }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-muted">No items listed</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Excluded</h6>
                                            @if(is_array($trip->excluded) && count($trip->excluded))
                                                <ul class="list-unstyled">
                                                    @foreach($trip->excluded as $item)
                                                        @php
                                                            $label = is_array($item) && isset($item['name']) ? $item['name'] : $item;
                                                        @endphp
                                                        <li>• {{ $label }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-muted">No items listed</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Pricing</h5>
                                    <p><strong>Price per Person (Double Occupancy):</strong>
                                        @if($trip->price_per_person)
                                            € {{ number_format($trip->price_per_person, 2) }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                    <p><strong>Single Room Addition:</strong>
                                        @if($trip->price_single_room_addition)
                                            € {{ number_format($trip->price_single_room_addition, 2) }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                    <p><strong>Cancellation Policy:</strong><br>
                                        <span class="text-muted">{{ $trip->cancellation_policy ?: 'Not set' }}</span>
                                    </p>
                                    <p><strong>Downpayment Policy:</strong><br>
                                        <span class="text-muted">{{ $trip->downpayment_policy ?: 'Not set' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Gallery</h3>
                        </div>
                        <div class="card-body">
                            @if($trip->gallery_images && count($trip->gallery_images) > 0)
                                <div class="row">
                                    @foreach($trip->gallery_images as $index => $image)
                                        <div class="col-6 mb-3">
                                            <img src="{{ asset('storage/' . $image) }}"
                                                 alt="Trip Image {{ $index + 1 }}"
                                                 class="img-thumbnail w-100"
                                                 style="height: 100px; object-fit: cover;">
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center">No images uploaded</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Provider</h3>
                        </div>
                        <div class="card-body">
                            @if($trip->provider_photo)
                                <div class="mb-3 text-center">
                                    <img src="{{ asset('storage/' . $trip->provider_photo) }}"
                                         alt="Provider"
                                         class="img-thumbnail"
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                </div>
                            @endif
                            <p><strong>Name:</strong> {{ $trip->provider_name ?: 'Not set' }}</p>
                            <p><strong>Experience:</strong><br>
                                <span class="text-muted">{{ $trip->provider_experience ?: 'Not set' }}</span>
                            </p>
                            <p><strong>Certifications:</strong><br>
                                <span class="text-muted">{{ $trip->provider_certifications ?: 'Not set' }}</span>
                            </p>
                            <p><strong>Boat Staff:</strong>
                                <span class="text-muted">{{ $trip->boat_staff ?: 'Not set' }}</span>
                            </p>
                            <p><strong>Guide Languages:</strong>
                                @php
                                    $guideLanguages = [];
                                    if (is_array($trip->guide_languages)) {
                                        foreach ($trip->guide_languages as $lang) {
                                            if (is_array($lang) && isset($lang['name'])) {
                                                $guideLanguages[] = $lang['name'];
                                            } elseif (is_string($lang)) {
                                                $guideLanguages[] = $lang;
                                            }
                                        }
                                    }
                                @endphp
                                @if(count($guideLanguages))
                                    {{ implode(', ', $guideLanguages) }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Availability</h3>
                        </div>
                        <div class="card-body">
                            @if($trip->availabilityDates && $trip->availabilityDates->count() > 0)
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Spots</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trip->availabilityDates as $row)
                                            <tr>
                                                <td>{{ $row->departure_date->format('Y-m-d') }}</td>
                                                <td>{{ $row->spots_available }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted">No availability rows added</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

