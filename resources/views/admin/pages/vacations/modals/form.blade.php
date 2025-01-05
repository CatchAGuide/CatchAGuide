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
                                    <input type="file" name="gallery[]" class="form-control" multiple accept="image/*" required onchange="previewImages(this)">
                                    <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Travel Information</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Best Travel Times</label>
                                    <input type="text" name="best_travel_times" class="form-control" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Surroundings Description</label>
                                    <textarea name="surroundings_description" class="form-control" rows="3" required></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Travel Included</label>
                                    <input type="text" name="travel_included" class="form-control" required>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Travel Options</label>
                                    <input type="text" name="travel_options" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- Distances and Amenities -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Location Features</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Airport Distance (km)</label>
                                    <input type="text" step="any" name="airport_distance" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Water Distance (km)</label>
                                    <input type="text" step="any" name="water_distance" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Shopping Distance (km)</label>
                                    <input type="text" step="any" name="shopping_distance" class="form-control" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Amenities</label>
                                    <input type="text" name="amenities" class="form-control tagify-input" required>
                                </div>
                            </div>
                        </div>

                        <!-- Accommodation Details -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Accommodation Details</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Accommodation Description</label>
                                    <textarea name="accommodation_description" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Catering Info</label>
                                    <input type="text" name="catering_info" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Living Area (m²)</label>
                                    <input type="text" step="any" name="living_area" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Bedroom Count</label>
                                    <input type="text" name="bedroom_count" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Bed Count</label>
                                    <input type="text" name="bed_count" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Max Persons</label>
                                    <input type="text" name="max_persons" class="form-control" required>
                                </div>
                                
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
                                    <input type="text" name="target_fish" class="form-control tagify-input" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Basic Fishing Description</label>
                                    <textarea name="basic_fishing_description" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Boat Description</label>
                                    <textarea name="boat_description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Equipment</label>
                                    <input type="text" name="equipment" class="form-control tagify-input" required>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Pricing</h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Package Price per Person</label>
                                    <input type="text" step="any" name="package_price_per_person" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Accommodation Price</label>
                                    <input type="text" step="any" name="accommodation_price" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Boat Rental Price</label>
                                    <input type="text" step="any" name="boat_rental_price" class="form-control">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Guiding Price</label>
                                    <input type="text" step="any" name="guiding_price" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Services -->
                        <div class="col-12 mb-4">
                            <h6 class="border-bottom pb-2">Services</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Additional Services</label>
                                    <input type="text" name="additional_services" class="form-control tagify-input">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Included Services</label>
                                    <input type="text" name="included_services" class="form-control tagify-input" required>
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