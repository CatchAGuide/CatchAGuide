@foreach($guidings as $guiding)
<div class="col-lg-4 col-md-6 col-12 mb-4">
    <div class="card h-100 border-0 shadow-sm">
        @php
            // Use the same logic as the main guiding-card.blade.php
            $galleryImages = $guiding->cached_gallery_images ?? json_decode($guiding->gallery_images);
            $firstImage = null;
            
            // First try thumbnail_path (most reliable for single image)
            if (!empty($guiding->thumbnail_path)) {
                $firstImage = $guiding->thumbnail_path;
            }
            // Then try to get first image from gallery
            elseif (!empty($galleryImages) && is_array($galleryImages) && count($galleryImages) > 0) {
                $firstImage = $galleryImages[0];
            }
            // Fallback to other image fields
            elseif (!empty($guiding->image)) {
                $firstImage = $guiding->image;
            }
            elseif (!empty($guiding->featured_image)) {
                $firstImage = $guiding->featured_image;
            }
            
            // Ensure the image path is properly formatted
            if ($firstImage && !str_starts_with($firstImage, 'http') && !str_starts_with($firstImage, '/')) {
                $firstImage = asset($firstImage);
            }
        @endphp
        
        @if($firstImage)
            <div class="position-relative">
                <img src="{{ $firstImage }}" 
                     alt="{{ $guiding->title }}" 
                     class="card-img-top"
                     style="height: 180px; object-fit: cover; width: 100%;"
                     onload="console.log('Image loaded successfully:', this.src);"
                     onerror="console.log('Image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-success">Selected</span>
                </div>
            </div>
        @else
            <div class="card-img-top d-flex align-items-center justify-content-center bg-gradient" 
                 style="height: 180px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                <div class="text-center text-muted">
                    <div class="fw-semibold text-dark">{{ $guiding->duration ?? 'N/A' }}</div>
                </div>
            </div>
        @endif
        
        <div class="card-body d-flex flex-column p-3">
            <h6 class="card-title fw-semibold mb-2 text-dark">
                {{ Str::limit($guiding->title, 45) }}
            </h6>
            
            <div class="mb-2">
                <small class="text-muted d-flex align-items-center">
                    <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                    {{ Str::limit($guiding->location, 30) }}
                </small>
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <small class="text-muted d-flex align-items-center">
                        <i class="fas fa-clock me-1 text-muted"></i>
                        {{ $guiding->duration ?? 'N/A' }}
                    </small>
                </div>
                <div class="col-6">
                    <small class="text-muted d-flex align-items-center">
                        <i class="fas fa-users me-1 text-muted"></i>
                        {{ $guiding->max_guests ?? 'N/A' }} pax
                    </small>
                </div>
            </div>
            
            @if($guiding->guidingTargets && $guiding->guidingTargets->count() > 0)
                <div class="mb-3">
                    @foreach($guiding->guidingTargets->take(2) as $target)
                        <span class="badge bg-light text-dark me-1 small">{{ $target->name }}</span>
                    @endforeach
                    @if($guiding->guidingTargets->count() > 2)
                        <span class="badge bg-secondary small">+{{ $guiding->guidingTargets->count() - 2 }}</span>
                    @endif
                </div>
            @endif
            
            <div class="mt-auto">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h6 text-primary fw-bold mb-0">
                            @if($guiding->price)
                                {{ number_format($guiding->price, 0) }}€
                            @else
                                On request
                            @endif
                        </div>
                        @if($guiding->price)
                            <small class="text-muted">per person</small>
                        @endif
                    </div>
                    <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}" 
                       class="btn btn-sm btn-outline-primary">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
