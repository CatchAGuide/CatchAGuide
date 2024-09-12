@include('pages.guidings.includes.styles.multi-step-form')
<div class="card">
    <div class="card-body">
        
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>
        <div class="step-wrapper">
            <div class="step-buttons">
                <div class="step-button active" data-step="1">
                    <i class="fas fa-ship"></i>
                    <p>Gallery</p>
                </div>
                <div class="step-button" data-step="2">
                    <i class="fas fa-info-circle"></i>
                    <p>Information</p>
                </div>
                <div class="step-button" data-step="3">
                    <i class="fas fa-fish"></i>
                    <p>Fish Details</p>
                </div>
                <div class="step-button" data-step="4">
                    <i class="fas fa-chart-line"></i>
                    <p>Expertise</p>
                </div>
                <div class="step-button" data-step="5">
                    <i class="fas fa-file-alt"></i>
                    <p>Description</p>
                </div>
                <div class="step-button" data-step="6">
                    <i class="fas fa-info-circle"></i>
                    <p>Other</p>
                </div>
                <div class="step-button" data-step="7">
                    <i class="fas fa-dollar-sign"></i>
                    <p>Pricing</p>
                </div>
                <div class="step-button" data-step="8">
                    <i class="fas fa-calendar-alt"></i>
                    <p>Schedule</p>
                </div>
            </div>

            <div class="step-line"></div>
        </div>

        <form action="{{ route('profile.newguiding.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Step 1 -->
            <div class="step active" id="step1">
                <h5>Upload images and set basic information</h5>

                <label for="title_image">Galery Images</label>
                <div class="file-upload-wrapper">
                    <input id="title_image" name="title_image[]" type="file" multiple onchange="previewImages(this);" />
                    <label for="title_image" class="file-upload-btn">Choose Files</label>
                    <div id="croppedImagesContainer"></div>
                </div>

                <div class="image-area" id="imagePreviewContainer"></div>
                <input type="hidden" name="primaryImage" id="primaryImageInput">

                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text">Location</span>
                        <input type="search" class="form-control" id="location" name="location" placeholder="Enter a city or any other location close to the area your fishing tour takes place" data-bs-toggle="tooltip" title="Enter the location where you offer your guiding service">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="country" id="country">
                        <input type="hidden" name="postal_code" id="postal_code">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text">Title</span>
                        <input type="text" class="form-control" id="title" name="title">
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step" id="step2">
                <h5>Provide details about your guiding service</h5>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="option-card" id="boatOption" onclick="selectOption('boat')">
                            <i class="fas fa-ship option-icon"></i>
                            <p class="option-label">Boat</p>
                            <input type="radio" name="type_of_fishing_radio" value="boat" class="d-none">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="option-card" id="shoreOption" onclick="selectOption('shore')">
                            <i class="fas fa-water option-icon"></i>
                            <p class="option-label">Shore</p>
                            <input type="radio" name="type_of_fishing_radio" value="shore" class="d-none">
                        </div>
                    </div>
                    <input type="hidden" name="type_of_fishing" id="type_of_fishing">
                </div>

                <div id="extraFields" style="display: none;">
                    <div class="form-group">
                        <label for="type_of_boat">Type of boat</label>
                        <div class="d-flex flex-wrap btn-group-toggle">
                            <input type="radio" name="type_of_boat" value="kayak" id="kayak">
                            <label for="kayak" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Kayak</label>
                            
                            <input type="radio" name="type_of_boat" value="belly_boat" id="belly_boat">
                            <label for="belly_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Belly boat</label>
                            
                            <input type="radio" name="type_of_boat" value="rowing_boat" id="rowing_boat">
                            <label for="rowing_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Rowing boat</label>
                            
                            <input type="radio" name="type_of_boat" value="drift_boat" id="drift_boat">
                            <label for="drift_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Drift boat</label>
                            
                            <input type="radio" name="type_of_boat" value="sportfishing_boat" id="sportfishing_boat">
                            <label for="sportfishing_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Sportfishing boat</label>
                            
                            <input type="radio" name="type_of_boat" value="yacht" id="yacht">
                            <label for="yacht" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Yacht</label>
                            
                            <input type="radio" name="type_of_boat" value="sailing_boat" id="sailing_boat">
                            <label for="sailing_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Sailing boat</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descriptions">Description</label>
                        <div class="btn-group-toggle">
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="seats" id="seats_checkbox">
                                <label for="seats_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Number of seats / capacity</label>
                                <textarea class="form-control extra-input" name="seats" placeholder="Enter number of seats or capacity"></textarea>
                            </div>
                    
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="length" id="length_checkbox">
                                <label for="length_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Length</label>
                                <textarea class="form-control extra-input" name="length" placeholder="Enter the length"></textarea>
                            </div>
                    
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="width" id="width_checkbox">
                                <label for="width_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Width</label>
                                <textarea class="form-control extra-input" name="width" placeholder="Enter the width"></textarea>
                            </div>
                    
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="year_built" id="year_built_checkbox">
                                <label for="year_built_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Year Built</label>
                                <textarea class="form-control extra-input" name="year_built" placeholder="Enter the year built"></textarea>
                            </div>
                    
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="engine_manufacturer" id="engine_manufacturer_checkbox">
                                <label for="engine_manufacturer_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Engine Manufacturer</label>
                                <textarea class="form-control extra-input" name="engine_manufacturer" placeholder="Enter the engine manufacturer"></textarea>
                            </div>
                    
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="engine_power" id="engine_power_checkbox">
                                <label for="engine_power_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Engine Power (hp)</label>
                                <textarea class="form-control extra-input" name="engine_power" placeholder="Enter the engine power (hp)"></textarea>
                            </div>
                    
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="max_speed" id="max_speed_checkbox">
                                <label for="max_speed_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Max Speed</label>
                                <textarea class="form-control extra-input" name="max_speed" placeholder="Enter the maximum speed"></textarea>
                            </div>
                    
                            <div class="btn-checkbox-container">
                                <input type="checkbox" name="descriptions[]" value="manufacturer" id="manufacturer_checkbox">
                                <label for="manufacturer_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Manufacturer</label>
                                <textarea class="form-control extra-input" name="manufacturer" placeholder="Enter the manufacturer"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group mt-2">
                            <span class="input-group-text">Extras</span>
                            <input  class="form-control" name="extras" id="extras" placeholder="Add extras..." data-bs-toggle="tooltip" title="Add any additional features or services you offer">
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step" id="step3">
                <h5>Specify fish species and fishing details</h5>
                
                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text">Target Fish</span>
                        <input type="text" class="form-control" name="target_fish" id="target_fish" data-role="tagsinput" placeholder="Add Target Fish...">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text">Methods</span>
                        <input type="text" class="form-control" name="methods" id="methods" data-role="tagsinput" placeholder="Select Methods...">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text">Water Types</span>
                        <input type="text" class="form-control" name="water_types" id="water_types" data-role="tagsinput" placeholder="Select Water Tyles...">
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step" id="step4">
                <h5>Describe your expertise and experience</h5>

                <div class="form-group">
                    <label for="experience_level">Experience Level</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="checkbox" name="experience_level[]" value="beginner" id="beginner">
                        <label for="beginner" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Beginner</label>
                        
                        <input type="checkbox" name="experience_level[]" value="advance" id="advance">
                        <label for="advance" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Advance</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text">Included without surcharge</span>
                        <input type="text" class="form-control" name="inclussions" id="inclussions" data-role="tagsinput" placeholder="Select inclussions...">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="style_of_fishing">Style Of Fishing</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="style_of_fishing" value="active" id="active">
                        <label for="active" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Active</label>
                        
                        <input type="radio" name="style_of_fishing" value="passive" id="passive">
                        <label for="passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Passive</label>
                        
                        <input type="radio" name="style_of_fishing" value="active_passive" id="active_passive">
                        <label for="active_passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Active / Passive</label>
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="step" id="step5">
                <h5>Write a detailed description of your service</h5>
                
                <div class="form-group">
                    <label for="desc_course_of_action">Tell your guests what they can expect from your fishing tour. How does a typical fishing tour look like?</label>
                    <textarea name="desc_course_of_action" id="desc_course_of_action" class="form-control" placeholder="course of action. . . ."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="desc_starting_time">Let your guest know when you typically begin with the fishing tour.</label>
                    <textarea name="desc_starting_time" id="desc_starting_time" class="form-control" placeholder="starting time. . . ."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="desc_meeting_point">Give your guests some information, where they will meet you after they have booked your fishing tour</label>
                    <textarea name="desc_meeting_point" id="desc_meeting_point" class="form-control" placeholder="meeting point. . . ."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="desc_tour_unique">Tell your guests about special highlights they can experience on a fishing tour with you</label>
                    <textarea name="desc_tour_unique" id="desc_tour_unique" class="form-control" placeholder="uniqueness. . . ."></textarea>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="step" id="step6">
                <h5>Add any additional information</h5>

                <div class="form-group">
                    <label for="other_information">Add a comment or additional information for your guests.</label>
                    <div class="btn-group-toggle">
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="child_friendly" id="child_friendly_checkbox">
                            <label for="child_friendly_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Child Friendly</label>
                            <textarea class="form-control extra-input" name="child_friendly" placeholder="Child friendly information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="disability_friendly" id="disability_friendly_checkbox">
                            <label for="disability_friendly_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Disability Friendly</label>
                            <textarea class="form-control extra-input" name="disability_friendly" placeholder="Disability friendly information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="no_smoking" id="no_smoking_checkbox">
                            <label for="no_smoking_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">No Smoking</label>
                            <textarea class="form-control extra-input" name="no_smoking" placeholder="No Smoking information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="no_alcohol" id="no_alcohol_checkbox">
                            <label for="no_alcohol_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">No Alcohol</label>
                            <textarea class="form-control extra-input" name="no_alcohol" placeholder="No Alcohol information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="keep_catch" id="keep_catch_checkbox">
                            <label for="keep_catch_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Keep Catch</label>
                            <textarea class="form-control extra-input" name="keep_catch" placeholder="Keep Catch information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="catch_release_allowed" id="catch_release_allowed_checkbox">
                            <label for="catch_release_allowed_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Catch & Release Allowed</label>
                            <textarea class="form-control extra-input" name="catch_release_allowed" placeholder="Catch & Release Allowed information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="catch_release_only" id="catch_release_only_checkbox">
                            <label for="catch_release_only_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Catch & Release Only</label>
                            <textarea class="form-control extra-input" name="catch_release_only" placeholder="Catch & Release Only information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="accomodation" id="accomodation_checkbox">
                            <label for="accomodation_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Accomodation</label>
                            <textarea class="form-control extra-input" name="accomodation" placeholder="Accomodation information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="campsite" id="campsite_checkbox">
                            <label for="campsite_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Campsite</label>
                            <textarea class="form-control extra-input" name="campsite" placeholder="Campsite information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="pick_up_service" id="pick_up_service_checkbox">
                            <label for="pick_up_service_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Pick Up Service</label>
                            <textarea class="form-control extra-input" name="pick_up_service" placeholder="Pick Up Service information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="other_information" id="others_information_checkbox">
                            <label for="others_information_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Others</label>
                            <textarea class="form-control extra-input" name="other_information" placeholder="Other information"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="requiements_taking_part">Add a comment or additional information for your guests.</label>
                    <div class="btn-group-toggle">
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="license_required" id="license_required_checkbox">
                            <label for="license_required_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">License or permit required</label>
                            <textarea class="form-control extra-input" name="license_required" placeholder="License or permit information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="specific_clothing" id="specific_clothing_checkbox">
                            <label for="specific_clothing_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Specific clothing required</label>
                            <textarea class="form-control extra-input" name="specific_clothing" placeholder="Specific clothing information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="certain_experience" id="certain_experience_checkbox">
                            <label for="certain_experience_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Certain experience required</label>
                            <textarea class="form-control extra-input" name="certain_experience" placeholder="Certain experience information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="manufacturer_requirements" id="manufacturer_requirements_checkbox">
                            <label for="manufacturer_requirements_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Others</label>
                            <textarea class="form-control extra-input" name="manufacturer_requirements" placeholder="Manufacturer information"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="recommended_preparation">Add a comment or additional information for your guests.</label>
                    <div class="btn-group-toggle">
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="sun_protection" id="sun_protection_checkbox">
                            <label for="sun_protection_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Sun Protection</label>
                            <textarea class="form-control extra-input" name="sun_protection" placeholder="Sun protection information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="food_drinks" id="food_drinks_checkbox">
                            <label for="food_drinks_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Food and Drinks</label>
                            <textarea class="form-control extra-input" name="food_drinks" placeholder="Food and Drinks information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="own_equipment" id="own_equipment_checkbox">
                            <label for="own_equipment_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Own Equipment</label>
                            <textarea class="form-control extra-input" name="own_equipment" placeholder="Own Equipment information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="specific_clothing_recommended" id="specific_clothing_recommended_checkbox">
                            <label for="specific_clothing_recommended_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Specific Clothing</label>
                            <textarea class="form-control extra-input" name="specific_clothing_recommended" placeholder="Specific clothing information"></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="others_recommended" id="others_recommended_checkbox">
                            <label for="others_recommended_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Others</label>
                            <textarea class="form-control extra-input" name="others_recommended" placeholder="Others information"></textarea>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>

            <!-- Step 7 -->
            <div class="step" id="step7">
                <h5>Set your pricing structure</h5>
                <div class="form-group">
                    <label for="tour_type">Tour Type</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="tour_type" value="private" id="private">
                        <label for="private" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Private tours only</label>
                        
                        <input type="radio" name="tour_type" value="shared" id="shared">
                        <label for="shared" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Shared tours possible</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="duration" value="half_day" id="half_day">
                        <label for="half_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Half Day</label>
                        
                        <input type="radio" name="duration" value="full_day" id="full_day">
                        <label for="full_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Full Day</label>
                        
                        <input type="radio" name="duration" value="multi_day" id="multi_day">
                        <label for="multi_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Multi Day</label>
                    </div>
                    <div id="duration_details" class="mt-3" style="display: none;">
                        <div class="input-group mt-2">
                            <span class="input-group-text">Number of hours:</span>
                            <input type="number" id="duration_hours" name="duration_hours" class="form-control" min="1" max="24">
                        </div>
                        <div class="input-group mt-2">
                            <span class="input-group-text">Number of days:</span>
                            <input type="number" id="duration_days" name="duration_days" class="form-control" min="2">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text">Number of guest</span>
                        <input type="number" class="form-control" id="no_guest" name="no_guest" placeholder="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="price">Pricing</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="price_type" value="per_person" id="per_person_checkbox">
                        <label for="per_person_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">per Person</label>
                        
                        <input type="radio" name="price_type" value="per_boat" id="per_boat_checkbox">
                        <label for="per_boat_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">per Boat</label>
                    </div>
                    
                    <div class="form-group" id="dynamic-price-fields-container"></div>
                </div>
                
                <div class="form-group">
                    <label for="extra_pricing">Extras <button type="button" id="add-extra" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i></button></label>
                    <div id="extras-container"></div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>

            <!-- Step 8 -->
            <div class="step" id="step8">
                <h5>Define your availability and booking options</h5>
                <div class="form-group">
                    <label for="allowed_booking_advance">Allowance of min. booking days in advance</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="allowed_booking_advance" value="same_day" id="same_day">
                        <label for="same_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">On the same day</label>
                        
                        <input type="radio" name="allowed_booking_advance" value="three_days" id="three_days">
                        <label for="three_days" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Three days upfront</label>
                        
                        <input type="radio" name="allowed_booking_advance" value="one_week" id="one_week">
                        <label for="one_week" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">One week upfront</label>
                        
                        <input type="radio" name="allowed_booking_advance" value="one_month" id="one_month">
                        <label for="one_month" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">One month upfront</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="booking_window">Booking window for how long in advance you allow bookings</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="booking_window" value="no_limitation" id="no_limitation">
                        <label for="no_limitation" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">No limitation</label>
                        
                        <input type="radio" name="booking_window" value="six_months" id="six_months">
                        <label for="six_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Six months in advance</label>
                        
                        <input type="radio" name="booking_window" value="nine_months" id="nine_months">
                        <label for="nine_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Nine months in advance</label>
                        
                        <input type="radio" name="booking_window" value="twelve_months" id="twelve_months">
                        <label for="twelve_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">12 months in advance</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="seasonal_trip">Seasonal Trip</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="seasonal_trip" value="season_year" id="season_year">
                        <label for="season_year" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(50% - 20px);">Available all year</label>
                        
                        <input type="radio" name="seasonal_trip" value="season_monthly" id="season_monthly">
                        <label for="season_monthly" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(50% - 20px);">Available on certain months only</label>
                    </div>
                    
                    <div id="monthly_selection" style="display: none;">
                        <div class="d-flex flex-wrap btn-group-toggle">
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                <div class="btn-checkbox-container" style="flex: 0 0 20%; max-width: 20%; padding: 5px;">
                                    <input type="checkbox" name="available_month[]" value="{{ strtolower($month) }}" id="avail_{{ strtolower($month) }}">
                                    <label for="avail_{{ strtolower($month) }}" class="btn btn-outline-primary btn-checkbox w-100">{{ $month }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                    <button type="submit" class="btn btn-success" id="submitBtn">Submit</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">Leave & Save to Draft</button>
                </div>
            </div>
        </form>
    </div>
</div>
@include('pages.guidings.includes.scripts.multi-step-form')
