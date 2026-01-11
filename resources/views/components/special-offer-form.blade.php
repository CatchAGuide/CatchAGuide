{{-- Special Offer Form Component - 3 Step Form --}}
<div id="special-offer-form" class="card">
    <div class="card-body">
        
        <div class="step-wrapper">
            <div class="step-buttons">
                <button type="button" class="step-button active" data-step="1">
                    <i class="fas fa-images"></i>
                    <span>Gallery & Info</span>
                </button>
                <button type="button" class="step-button" data-step="2">
                    <i class="fas fa-link"></i>
                    <span>Assemblies</span>
                </button>
                <button type="button" class="step-button" data-step="3">
                    <i class="fas fa-tags"></i>
                    <span>Included & Pricing</span>
                </button>
            </div>
            <div class="step-line"></div>
        </div>
        
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ $formAction ?? (isset($formData['id']) && $formData['id'] ? route('admin.special-offers.update', $formData['id']) : route('admin.special-offers.store')) }}" method="POST" id="specialOfferForm" enctype="multipart/form-data">
            @csrf
            @if(isset($formData['id']) && $formData['id'])
                @method('PUT')
            @endif
            <meta name="csrf-token" content="{{ csrf_token() }}">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <input type="hidden" name="target_redirect" id="target_redirect" value="{{ $targetRedirect ?? route('admin.special-offers.index') }}">
            <input type="hidden" name="is_update" id="is_update" value="{{ $formData['is_update'] ?? 0 }}">
            <input type="hidden" name="is_draft" id="is_draft" value="{{ isset($formData['status']) && $formData['status'] == 'draft' ? 1 : 0 }}">
            <input type="hidden" name="special_offer_id" id="special_offer_id" value="{{ $formData['id'] ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $formData['thumbnail_path'] ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ isset($formData['gallery_images']) && is_array($formData['gallery_images']) ? json_encode($formData['gallery_images']) : (isset($formData['gallery_images']) ? $formData['gallery_images'] : '') }}">
            <input type="hidden" name="user_id" id="user_id" value="{{ $formData['user_id'] ?? auth()->id() }}">
            <input type="hidden" name="status" id="status" value="{{ $formData['status'] ?? 'active' }}">
            <input type="hidden" id="image_list" name="image_list">
            <input type="hidden" name="pricing" id="pricing_input" value="{{ isset($formData['pricing']) ? json_encode($formData['pricing']) : '' }}">

            <!-- Step 1: Gallery, Location and Title -->
            <div class="step active" id="step1">
                <h5>Gallery, Location & Title</h5>

                <label for="title_image" class="form-label fw-bold fs-5">
                    Upload Images
                    <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                       title="Upload images for your special offer"></i>
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple accept="image/*" />
                            <input id="cropped_image" name="cropped_image[]" type="file" multiple hidden/>
                            <label for="title_image" class="file-upload-btn">Choose Files</label>
                        </div>
                        <div id="croppedImagesContainer"></div>
                    </div>

                    <div class="image-area" id="imagePreviewContainer" style="display: none;"></div>
                    <input type="hidden" name="primaryImage" id="primaryImageInput">
                </div>

                <hr>

                <div class="form-group">
                    <label for="location" class="form-label fw-bold fs-5">
                        Location
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Enter the location of your special offer"></i>
                    </label>
                    <input type="search" class="form-control" id="location" name="location" value="{{ $formData['location'] ?? '' }}" placeholder="Enter location">
                    <input type="hidden" name="latitude" id="latitude" value="{{ $formData['latitude'] ?? $formData['lat'] ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $formData['longitude'] ?? $formData['lng'] ?? '' }}">
                    <input type="hidden" name="country" id="country" value="{{ $formData['country'] ?? '' }}">
                    <input type="hidden" name="city" id="city" value="{{ $formData['city'] ?? '' }}">
                    <input type="hidden" name="region" id="region" value="{{ $formData['region'] ?? '' }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        Title
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Enter a catchy title for your special offer"></i>
                    </label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $formData['title'] ?? '' }}" placeholder="Enter special offer title">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn1">
                            Save as Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="nextBtn1">
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Assembly of Accommodation, Rental boat, guidings -->
            <div class="step" id="step2">
                <h5>Assemble Services</h5>

                <div class="form-group">
                    <label for="accommodations" class="form-label fw-bold fs-5">
                        Accommodations
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select accommodations to include in this special offer"></i>
                    </label>
                    <input type="text" class="form-control" name="accommodations" id="accommodations" 
                           placeholder="Type to search accommodations..." 
                           value="{{ isset($formData['accommodations']) && is_array($formData['accommodations']) ? implode(',', array_map(function($id) use ($accommodations) { 
                               $acc = $accommodations->firstWhere('id', $id);
                               return $acc ? '(' . $acc->id . ') | ' . $acc->title : '';
                           }, $formData['accommodations'])) : '' }}">
                    <input type="hidden" name="accommodations_ids" id="accommodations_ids" 
                           value="{{ isset($formData['accommodations']) && is_array($formData['accommodations']) ? implode(',', $formData['accommodations']) : '' }}">
                    
                    <!-- Selected Accommodations Cards Display -->
                    <div class="form-group mt-3" id="selected-accommodations-container" style="display: none;">
                        <label class="form-label fw-bold fs-5">Selected Accommodations</label>
                        <div id="selected-accommodations-cards" class="row">
                            <!-- Selected accommodation cards will be displayed here -->
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="rental_boats" class="form-label fw-bold fs-5">
                        Rental Boats
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select rental boats to include in this special offer"></i>
                    </label>
                    <input type="text" class="form-control" name="rental_boats" id="rental_boats" 
                           placeholder="Type to search rental boats..." 
                           value="{{ isset($formData['rental_boats']) && is_array($formData['rental_boats']) ? implode(',', array_map(function($id) use ($rentalBoats) { 
                               $rb = $rentalBoats->firstWhere('id', $id);
                               return $rb ? '(' . $rb->id . ') | ' . $rb->title : '';
                           }, $formData['rental_boats'])) : '' }}">
                    <input type="hidden" name="rental_boats_ids" id="rental_boats_ids" 
                           value="{{ isset($formData['rental_boats']) && is_array($formData['rental_boats']) ? implode(',', $formData['rental_boats']) : '' }}">
                    
                    <!-- Selected Rental Boats Cards Display -->
                    <div class="form-group mt-3" id="selected-rental-boats-container" style="display: none;">
                        <label class="form-label fw-bold fs-5">Selected Rental Boats</label>
                        <div id="selected-rental-boats-cards" class="row">
                            <!-- Selected rental boat cards will be displayed here -->
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="guidings" class="form-label fw-bold fs-5">
                        Guidings
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Select guidings to include in this special offer"></i>
                    </label>
                    <input type="text" class="form-control" name="guidings" id="guidings" 
                           placeholder="Type to search guidings..." 
                           value="{{ isset($formData['guidings']) && is_array($formData['guidings']) ? implode(',', array_map(function($id) use ($guidings) { 
                               $g = $guidings->firstWhere('id', $id);
                               return $g ? '(' . $g->id . ') | ' . $g->title : '';
                           }, $formData['guidings'])) : '' }}">
                    <input type="hidden" name="guidings_ids" id="guidings_ids" 
                           value="{{ isset($formData['guidings']) && is_array($formData['guidings']) ? implode(',', $formData['guidings']) : '' }}">
                    
                    <!-- Selected Guidings Cards Display -->
                    <div class="form-group mt-3" id="selected-guidings-container" style="display: none;">
                        <label class="form-label fw-bold fs-5">Selected Guidings</label>
                        <div id="selected-guidings-cards" class="row">
                            <!-- Selected guiding cards will be displayed here -->
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn2">
                            Save as Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn2">
                                Previous
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn2">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: "What's included?" + Pricing -->
            <div class="step" id="step3">
                <h5>What's Included & Pricing</h5>

                <div class="form-group">
                    <label for="whats_included" class="form-label fw-bold fs-5">
                        What's Included?
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" 
                           title="Add items that are included in this special offer (use tagify)"></i>
                    </label>
                    <input type="text" class="form-control" name="whats_included" id="whats_included" 
                           placeholder="Add items (press Enter to add each item)"
                           value="{{ isset($formData['whats_included']) && is_array($formData['whats_included']) ? implode(',', $formData['whats_included']) : '' }}">
                    <small class="form-text text-muted">Press Enter after each item to add it as a tag</small>
                </div>

                <hr>

                <h5>Pricing</h5>
                
                <input type="hidden" name="price_type" id="price_type" value="fixed">
                <input type="hidden" name="currency" id="currency" value="EUR">

                <div class="form-group">
                    <div id="pricing-container">
                        <div class="pricing-tier mb-3 p-3 border rounded">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Price</label>
                                    <input type="number" class="form-control pricing-amount" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger mt-2 remove-tier" style="display: none;">Remove</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="add-pricing-tier">Add Pricing Tier</button>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn3">
                            Save as Draft
                        </button>
                    </div>
                    <div class="right-buttons">
                        <div class="row-button">
                            <button type="button" class="btn btn-info" id="prevBtn3">
                                Previous
                            </button>
                            <div></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn3" onclick="document.getElementById('is_draft').value = '0';">
                            Submit & Publish
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@include('components.special-offer-form-scripts')

