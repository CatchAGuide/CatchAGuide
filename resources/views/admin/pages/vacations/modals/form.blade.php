<div class="modal fade" id="addVacationModal" tabindex="-1" aria-labelledby="addVacationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVacationModalLabel">Add New Vacation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.vacations.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Location Details -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Location Details</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <input id="location" class="form-control" type="text" placeholder="Search Location" name="location" autocomplete="on" required>
                                    <input type="hidden" name="city">
                                    <input type="hidden" name="country">
                                    <input type="hidden" name="region">
                                    <input type="hidden" name="latitude">
                                    <input type="hidden" name="longitude">
                                </div>
                            </div>
                        </div>

                        <!-- Gallery -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Gallery</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Gallery Images</label>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <input type="file" name="gallery[]" class="form-control" multiple accept="image/*" onchange="previewImages(this)">
                                        <button type="button" class="btn btn-danger ms-2" onclick="removeAllImages()">
                                            <i class="fas fa-trash"></i> Remove All
                                        </button>
                                    </div>
                                    <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2">
                                        <!-- Existing images will be loaded here -->
                                    </div>
                                    <input type="hidden" name="existing_gallery" id="existingGallery">
                                </div>
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Travel Information</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Best Travel Times</label>
                                    <input type="text" name="best_travel_times" class="form-control">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Surroundings Description</label>
                                    <textarea id="surroundings_description" cols="30" rows="10" class="form-control" name="surroundings_description"></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Travel Included</label>
                                    <input type="text" name="travel_included" class="form-control">
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Travel Options</label>
                                    <input type="text" name="travel_options" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Location Features</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Airport Distance (km)</label>
                                    <input type="text" step="any" name="airport_distance" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Water Distance (km)</label>
                                    <input type="text" step="any" name="water_distance" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Shopping Distance (km)</label>
                                    <input type="text" step="any" name="shopping_distance" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Accommodation Details -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Accommodation Details</h6>
                            <div class="row">                                
                                <!-- Checkboxes in a more organized layout -->
                                <div class="col-12">
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input type="checkbox" name="pets_allowed" class="form-check-input" value="1" id="petsAllowed">
                                            <label class="form-check-label" for="petsAllowed">Pets Allowed</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="smoking_allowed" class="form-check-input" value="1" id="smokingAllowed">
                                            <label class="form-check-label" for="smokingAllowed">Smoking Allowed</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="disability_friendly" class="form-check-input" value="1" id="disabilityFriendly">
                                            <label class="form-check-label" for="disabilityFriendly">Disability Friendly</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="has_boat" class="form-check-input" value="1" id="hasBoat">
                                            <label class="form-check-label" for="hasBoat">Has Boat</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="has_guiding" class="form-check-input" value="1" id="hasGuiding">
                                            <label class="form-check-label" for="hasGuiding">Has Guiding</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fishing Details -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Fishing Information</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Target Fish</label>
                                    <input type="text" name="target_fish" class="form-control tagify-input">
                                </div>
                            </div>
                        </div>

                        <!-- Services -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Services</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Included Services</label>
                                    <input type="text" name="included_services" class="form-control tagify-input">
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Sections -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Additional Options</h6>
                            
                            <!-- Extras Section -->
                            <div class="section-wrapper mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Extras</h6>
                                    <button type="button" class="btn btn-sm btn-primary add-item" data-type="extra">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="extra-items" class="items-container">
                                    <!-- Dynamic items will be added here -->
                                </div>
                            </div>

                            <!-- Accommodations Section -->
                            <div class="section-wrapper mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Accommodations</h6>
                                    <button type="button" class="btn btn-sm btn-primary add-item" data-type="accommodation">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="accommodation-items" class="items-container">
                                    <!-- Dynamic items will be added here -->
                                </div>
                            </div>

                            <!-- Boats Section -->
                            <div class="section-wrapper mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Boats</h6>
                                    <button type="button" class="btn btn-sm btn-primary add-item" data-type="boat">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="boat-items" class="items-container">
                                    <!-- Dynamic items will be added here -->
                                </div>
                            </div>

                            <!-- Packages Section -->
                            <div class="section-wrapper mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Packages</h6>
                                    <button type="button" class="btn btn-sm btn-primary add-item" data-type="package">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="package-items" class="items-container">
                                    <!-- Dynamic items will be added here -->
                                </div>
                            </div>

                            <!-- Guidings Section -->
                            <div class="section-wrapper mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Guidings</h6>
                                    <button type="button" class="btn btn-sm btn-primary add-item" data-type="guiding">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="guiding-items" class="items-container">
                                    <!-- Dynamic items will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Vacation</button>
                </div>
            </form>
        </div>
    </div>
</div> 