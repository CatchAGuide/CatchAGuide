@foreach($guidings as $guiding)
<div class="col-md-4 col-sm-6 col-12 mb-4">
    <div class="card h-100" style="border: 1px solid #dee2e6; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        @php
            $galleryImages = $guiding->cached_gallery_images ?? json_decode($guiding->gallery_images ?? '[]');
            $firstImage = !empty($galleryImages) ? $galleryImages[0] : null;
        @endphp
        
        @if($firstImage)
            <img src="{{ $firstImage }}" 
                 alt="{{ $guiding->title }}" 
                 class="card-img-top"
                 style="height: 180px; object-fit: cover;">
        @else
            <div class="card-img-top d-flex align-items-center justify-content-center bg-primary text-white" 
                 style="height: 180px;">
                <div class="text-center">
                    <i class="fas fa-fish fa-2x mb-2"></i>
                    <div>{{ $guiding->duration ?? 'N/A' }}</div>
                </div>
            </div>
        @endif
        
        <div class="card-body">
            <h6 class="card-title fw-bold mb-2" style="font-size: 1rem; line-height: 1.3;">
                {{ Str::limit($guiding->title, 50) }}
            </h6>
            
            <div class="mb-2">
                <small class="text-muted">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    {{ Str::limit($guiding->location, 25) }}
                </small>
            </div>
            
            <div class="mb-2">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    {{ $guiding->duration ?? 'N/A' }}
                </small>
            </div>
            
            <div class="mb-2">
                <small class="text-muted">
                    <i class="fas fa-users me-1"></i>
                    {{ $guiding->max_guests ?? 'N/A' }} persons
                </small>
            </div>
            
            @if($guiding->guidingTargets && $guiding->guidingTargets->count() > 0)
                <div class="mb-3">
                    @foreach($guiding->guidingTargets->take(2) as $target)
                        <span class="badge bg-light text-dark me-1" style="font-size: 0.7rem;">{{ $target->name }}</span>
                    @endforeach
                    @if($guiding->guidingTargets->count() > 2)
                        <span class="badge bg-secondary" style="font-size: 0.7rem;">+{{ $guiding->guidingTargets->count() - 2 }}</span>
                    @endif
                </div>
            @endif
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="h6 text-primary fw-bold mb-0">
                        @if($guiding->price)
                            {{ number_format($guiding->price, 0) }}€
                        @else
                            Price on request
                        @endif
                    </span>
                    @if($guiding->price)
                        <small class="text-muted d-block">per person</small>
                    @endif
                </div>
                <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}" 
                   class="btn btn-sm btn-outline-primary">
                    View
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach
