@include('pages.guidings.includes.styles.multi-step-form-style')
<div class="card">
    <div class="card-body">
        
        <div class="step-wrapper">
            <div class="step-buttons">
                <div class="step-button active" data-step="1">
                    <i class="fas fa-ship"></i>
                </div>
                <div class="step-button" data-step="2">
                    <i class="fas fa-water"></i>
                </div>
                <div class="step-button" data-step="3">
                    <i class="fas fa-anchor"></i>
                </div>
                {{-- <div class="step-button" data-step="4">
                    <i class="fas fa-chart-line"></i>
                </div> --}}
                <div class="step-button" data-step="4">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="step-button" data-step="5">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="step-button" data-step="6">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="step-button" data-step="7">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <div class="step-line"></div>
        </div>
        <div id="error-container" class="alert alert-danger" style="display: none;"></div>

        <form action="{{ route('profile.newguiding.store') }}" method="POST" id="newGuidingForm" enctype="multipart/form-data">
            @csrf
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

            <input type="hidden" name="is_update" id="is_update" value="{{ $formData['is_update'] ?? 0 }}">
            <input type="hidden" name="guiding_id" id="guiding_id" value="{{ $formData['id'] ?? 0 }}">
            <input type="hidden" name="thumbnail_path" id="thumbnail_path" value="{{ $formData['thumbnail_path'] ?? '' }}">
            <input type="hidden" name="existing_images" id="existing_images" value="{{ $formData['galery_images'] ?? "" }}">

            <!-- Step 1 -->
            <div class="step active" id="step1">
                <h5>Upload images and set basic information</h5>

                <label for="title_image" class="form-label fw-bold fs-5">
                    Gallery Image
                    <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title='Choose a title which describes your tour best. Include for example the location, the target fish, the water name, etc. Example: "Fishing tour in Amsterdam for Perch & Zander".'></i>
                </label>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="file-upload-wrapper">
                            <input id="title_image" name="title_image[]" type="file" multiple />
                            <label for="title_image" class="file-upload-btn">Choose Files</label>
                        </div>
                        <div id="croppedImagesContainer"></div>
                    </div>

                    <div class="image-area" id="imagePreviewContainer"></div>
                    <input type="hidden" name="primaryImage" id="primaryImageInput">

                </div>

                <hr>

                <div class="form-group">
                    <label for="location" class="form-label fw-bold fs-5">
                        Location
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Enter a city or a region which is close to the place where your fishing tour takes place."></i>
                    </label>
                    <input type="search" class="form-control" id="location" name="location" value="{{ $formData['location'] ?? '' }}" placeholder="Enter a city or any other location close to the area your fishing tour takes place">
                    <input type="hidden" name="latitude" id="latitude" value="{{ $formData['latitude'] ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $formData['longitude'] ?? '' }}">
                    <input type="hidden" name="country" id="country" value="{{ $formData['country'] ?? '' }}">
                    <input type="hidden" name="postal_code" id="postal_code" value="{{ $formData['postal_code'] ?? '' }}">
                </div>

                <hr>

                <div class="form-group">
                    <label for="title" class="form-label fw-bold fs-5">
                        Title
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Chose a title which describes your tour best. Include for example the location, the target fish, the water name, etc. Example: "Fishing tour in Amsterdam for Perch & Zander"."></i>
                    </label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $formData['title'] ?? '' }}" placeholder="Enter a catchy title for your fishing tour">
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit</button>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step" id="step2">
                <h5>Type of fishing and boat description</h5>

                <div class="form-group">
                    <label for="type_of_fishing" class="form-label fw-bold fs-5">
                        Type of Fishing
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Will your fishing tour take place from the shore or will you take your guests fishing from a boat/ watercraft?"></i>
                    </label>
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
                                <i class="fas fa-umbrella-beach option-icon"></i>
                                <p class="option-label">Shore</p>
                                <input type="radio" name="type_of_fishing_radio" value="shore" class="d-none">
                            </div>
                        </div>
                        <input type="hidden" name="type_of_fishing" id="type_of_fishing">
                    </div>
                    <input type="hidden" name="type_of_fishing" id="type_of_fishing">
                </div>

                <div id="extraFields" style="display: none;">
                    <div class="form-group">
                        
                        <label for="type_of_boat" class="form-label fw-bold fs-5">
                            Type of boat
                            <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="On what kind of boat or watercraft does your fishing tour take place?"></i>
                        </label>
                        <div class="d-flex flex-wrap btn-group-toggle">
                            <input type="radio" name="type_of_boat" value="Kayak" id="kayak">
                            <label for="kayak" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Kayak</label>
                            
                            <input type="radio" name="type_of_boat" value="Belly Boat" id="belly_boat">
                            <label for="belly_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Belly boat</label>
                            
                            <input type="radio" name="type_of_boat" value="Rowing Boat" id="rowing_boat">
                            <label for="rowing_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Rowing boat</label>
                            
                            <input type="radio" name="type_of_boat" value="Drift Boat" id="drift_boat">
                            <label for="drift_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Drift boat</label>
                            
                            <input type="radio" name="type_of_boat" value="Sport Fishing Boat" id="sportfishing_boat">
                            <label for="sportfishing_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Sportfishing boat</label>
                            
                            <input type="radio" name="type_of_boat" value="Yacht" id="yacht">
                            <label for="yacht" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Yacht</label>
                            
                            <input type="radio" name="type_of_boat" value="Sailing Boat" id="sailing_boat">
                            <label for="sailing_boat" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Sailing boat</label>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="descriptions" class="form-label fw-bold fs-5">
                            Boat description
                            <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title='Tell your guests more about your boat by adding detailed information for each aspect.'></i>
                        </label>
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

                    <hr>

                    <div class="form-group">
                        <label for="extras" class="form-label fw-bold fs-5">
                            Extras and boat equipment
                            <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Here you can add extra equipment which the guests can find on your boat."></i>
                        </label>
                        <input  class="form-control" name="extras" id="extras" placeholder="Add extras..." data-bs-toggle="tooltip" title="Here you can add extra equipment which the guests can find on your boat.">
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit</button>
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step" id="step3">
                <h5>Specify target fish and fishing method</h5>
                
                <div class="form-group">
                    <label for="target_fish" class="form-label fw-bold fs-5">
                        Target Fish
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Add all target fish which a guest will likely to catch during your fishing tour."></i>
                    </label>
                    <input type="text" class="form-control" name="target_fish" id="target_fish" data-role="tagsinput" placeholder="Add Target Fish...">
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="methods" class="form-label fw-bold fs-5">
                        Fishing Methods
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Fishing with which methods can guests expect during a fishing tour with you? Add all fishing methods which apply."></i>
                    </label>
                    <input type="text" class="form-control" name="methods" id="methods" data-role="tagsinput" placeholder="Select Methods...">
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label for="style_of_fishing" class="form-label fw-bold fs-5">
                        Style Of Fishing
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="What style of fishing is your guiding designed for? Your fishing trip can also be designed foractive and passive fishing together. "></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="style_of_fishing" value="active" id="active">
                        <label for="active" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Active</label>
                        
                        <input type="radio" name="style_of_fishing" value="passive" id="passive">
                        <label for="passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Passive</label>
                        
                        <input type="radio" name="style_of_fishing" value="active_passive" id="active_passive">
                        <label for="active_passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Active / Passive</label>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="water_types" class="form-label fw-bold fs-5">
                        Water Types
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Choose the water type on which your fishing tour takes place most of the time."></i>
                    </label>
                    <input type="text" class="form-control" name="water_types" id="water_types" data-role="tagsinput" placeholder="Select Water Tyles...">
                </div>

                <hr>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit</button>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            {{-- <div class="step" id="step4"> --}}
                {{-- <h5>Describe your expertise and experience</h5> --}}

                {{-- <div class="form-group">
                    <label for="experience_level" class="form-label fw-bold fs-5">
                        Experience Level
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="What experience level is your guiding designed for? Your fishing trip can also be designed for beginners and advanced anglers together. "></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="checkbox" name="experience_level[]" value="beginner" id="beginner">
                        <label for="beginner" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Beginner</label>
                        
                        <input type="checkbox" name="experience_level[]" value="advance" id="advance">
                        <label for="advance" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Advance</label>
                    </div>
                </div> --}}

                {{-- <hr>
                
                <div class="form-group">
                    <label for="inclussions" class="form-label fw-bold fs-5">
                        Inclusions
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Chose all extras which are included in your fishing tour without additional price charges."></i>
                    </label>
                    <input type="text" class="form-control" name="inclussions" id="inclussions" data-role="tagsinput" placeholder="Select inclussions...">
                </div> --}}

                {{-- <hr>
                
                <div class="form-group">
                    <label for="style_of_fishing" class="form-label fw-bold fs-5">
                        Style Of Fishing
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="What style of fishing is your guiding designed for? Your fishing trip can also be designed foractive and passive fishing together. "></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="style_of_fishing" value="active" id="active">
                        <label for="active" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Active</label>
                        
                        <input type="radio" name="style_of_fishing" value="passive" id="passive">
                        <label for="passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Passive</label>
                        
                        <input type="radio" name="style_of_fishing" value="active_passive" id="active_passive">
                        <label for="active_passive" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Active / Passive</label>
                    </div>
                </div> --}}

                {{-- <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit</button>
                    </div>
                </div>
            </div> --}}

            <!-- Step 5 -->
            <div class="step" id="step4">
                <h5>Write a detailed description of your service</h5>
                
                <div class="form-group">
                    <label for="desc_course_of_action" class="form-label fw-bold fs-5">
                        Course of action
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Tell your guests more about your fishing tour. What can they expect?"></i>
                    </label>
                    <textarea name="desc_course_of_action" id="desc_course_of_action" class="form-control" placeholder="Tell your guests what they can expect from your fishing tour. How does a typical fishing tour look like?">{{ $formData['desc_course_of_action'] ?? '' }}</textarea>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="desc_starting_time" class="form-label fw-bold fs-5">
                        Starting time
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Let your guest know when you typically begin with the fishing tour."></i>
                    </label>
                    <textarea name="desc_starting_time" id="desc_starting_time" class="form-control" placeholder="Let your guests know when you typically begin with the fishing tour.">{{ $formData['desc_starting_time'] ?? '' }}</textarea>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="desc_meeting_point" class="form-label fw-bold fs-5">
                        Meeting point
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Give your guests some information, where they will meet you after they have booked your fishing tour."></i>
                    </label>
                    <textarea name="desc_meeting_point" id="desc_meeting_point" class="form-control" placeholder="Give your guests information about where they will meet you after booking your fishing tour.">{{ $formData['desc_meeting_point'] ?? '' }}</textarea>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="desc_tour_unique" class="form-label fw-bold fs-5">
                        Tour highlights
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Tell your guests about special highlights they can experience on a fishing tour with you."></i>
                    </label>
                    <textarea name="desc_tour_unique" id="desc_tour_unique" class="form-control" placeholder="Tell your guests about special highlights they can experience on a fishing tour with you.">{{ $formData['desc_tour_unique'] ?? '' }}</textarea>
                </div>
                
                @if(isset($formData) && $formData['is_update'] == 1)
                    <div class="form-group">
                        <label for="long_description">Overall summary of the service and what it offers</label>
                        <textarea name="long_description" id="long_description" class="form-control" placeholder="course of action. . . ." readonly style="width: 100%; height: auto; min-height: 100px;" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'">{{ $formData['long_description'] ?? '' }}</textarea>
                    </div>

                    <hr>
                @endif

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit</button>
                    </div>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="step" id="step5">
                <h5>Add any additional information</h5>

                <div class="form-group">
                    <label for="group" class="form-label fw-bold fs-5">
                        Other Information
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Add all other information about your fishing tour which you like to tell your guests about."></i>
                    </label>
                    <div class="btn-group-toggle">
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="child_friendly" id="child_friendly_checkbox">
                            <label for="child_friendly_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Child Friendly</label>
                            <textarea class="form-control extra-input" name="child_friendly" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="disability_friendly" id="disability_friendly_checkbox">
                            <label for="disability_friendly_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Disability Friendly</label>
                            <textarea class="form-control extra-input" name="disability_friendly" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="no_smoking" id="no_smoking_checkbox">
                            <label for="no_smoking_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">No Smoking</label>
                            <textarea class="form-control extra-input" name="no_smoking" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="no_alcohol" id="no_alcohol_checkbox">
                            <label for="no_alcohol_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">No Alcohol</label>
                            <textarea class="form-control extra-input" name="no_alcohol" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="keep_catch" id="keep_catch_checkbox">
                            <label for="keep_catch_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Keep Catch</label>
                            <textarea class="form-control extra-input" name="keep_catch" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="catch_release_allowed" id="catch_release_allowed_checkbox">
                            <label for="catch_release_allowed_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Catch & Release Allowed</label>
                            <textarea class="form-control extra-input" name="catch_release_allowed" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="catch_release_only" id="catch_release_only_checkbox">
                            <label for="catch_release_only_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Catch & Release Only</label>
                            <textarea class="form-control extra-input" name="catch_release_only" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="accomodation" id="accomodation_checkbox">
                            <label for="accomodation_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Accomodation</label>
                            <textarea class="form-control extra-input" name="accomodation" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="campsite" id="campsite_checkbox">
                            <label for="campsite_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Campsite</label>
                            <textarea class="form-control extra-input" name="campsite" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="pick_up_service" id="pick_up_service_checkbox">
                            <label for="pick_up_service_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Pick Up Service</label>
                            <textarea class="form-control extra-input" name="pick_up_service" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="other_information[]" value="recommended_others" id="others_information_checkbox">
                            <label for="others_information_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Others</label>
                            <textarea class="form-control extra-input" name="recommended_others" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="requiements_taking_part" class="form-label fw-bold fs-5">
                        Requirements for taking part
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Mention all requirements which your guests need to take part in your fishing tour such as specific licenses, equipment, a special experience level or skill, etc."></i>
                    </label>
                    <div class="btn-group-toggle">
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="license_required" id="license_required_checkbox">
                            <label for="license_required_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">License or permit required</label>
                            <textarea class="form-control extra-input" name="license_required" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="specific_clothing" id="specific_clothing_checkbox">
                            <label for="specific_clothing_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Specific clothing required</label>
                            <textarea class="form-control extra-input" name="specific_clothing" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="certain_experience" id="certain_experience_checkbox">
                            <label for="certain_experience_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Certain experience required</label>
                            <textarea class="form-control extra-input" name="certain_experience" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="requiements_taking_part[]" value="manufacturer_requirements" id="manufacturer_requirements_checkbox">
                            <label for="manufacturer_requirements_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Others</label>
                            <textarea class="form-control extra-input" name="manufacturer_requirements" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="recommended_preparation" class="form-label fw-bold fs-5">
                        Recommended preparation
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="How can your guests prepare themselves for a fishing tour with you? Choose all things your guests should keep in mind when planning the tour."></i>
                    </label>
                    <div class="btn-group-toggle">
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="sun_protection" id="sun_protection_checkbox">
                            <label for="sun_protection_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Sun Protection</label>
                            <textarea class="form-control extra-input" name="sun_protection" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="food_drinks" id="food_drinks_checkbox">
                            <label for="food_drinks_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Food and Drinks</label>
                            <textarea class="form-control extra-input" name="food_drinks" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="own_equipment" id="own_equipment_checkbox">
                            <label for="own_equipment_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Own Equipment</label>
                            <textarea class="form-control extra-input" name="own_equipment" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="specific_clothing_recommended" id="specific_clothing_recommended_checkbox">
                            <label for="specific_clothing_recommended_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Specific Clothing</label>
                            <textarea class="form-control extra-input" name="specific_clothing_recommended" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                        
                        <div class="btn-checkbox-container">
                            <input type="checkbox" name="recommended_preparation[]" value="others_recommended" id="others_recommended_checkbox">
                            <label for="others_recommended_checkbox" class="btn btn-outline-primary m-2 btn-checkbox">Others</label>
                            <textarea class="form-control extra-input" name="others_recommended" placeholder="Add a comment or additional information for your guests."></textarea>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit</button>
                    </div>
                </div>
            </div>

            <!-- Step 7 -->
            <div class="step" id="step6">
                <h5>Set your pricing structure</h5>
                <div class="form-group">
                    <label for="tour_type" class="form-label fw-bold fs-5">
                        Tour Type
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Is your fishing tour a private tour or will it be possible that other guests can join the same tour so that your guests fish together in a group?"></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="tour_type" value="private" id="private">
                        <label for="private" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Private tours only</label>
                        
                        <input type="radio" name="tour_type" value="shared" id="shared">
                        <label for="shared" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Shared tours possible</label>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="duration" class="form-label fw-bold fs-5">
                        Duration Type
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Tell your guests whether your tour will take a half, a full or more than one day. Enter the amout of hours or days."></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="duration" value="half_day" id="half_day">
                        <label for="half_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Half Day</label>
                        
                        <input type="radio" name="duration" value="full_day" id="full_day">
                        <label for="full_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Full Day</label>
                        
                        <input type="radio" name="duration" value="multi_day" id="multi_day">
                        <label for="multi_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Multi Day</label>
                    </div>
                    <div id="duration_details" class="mt-3" style="display: none;">
                        <div id="hours_input" class="input-group mt-2">
                            <span class="input-group-text">Number of hours:</span>
                            <input type="number" id="duration_hours" name="duration_hours" class="form-control" value="{{ $formData['duration_hours'] ?? '' }}" min="1" max="24">
                        </div>
                        <div id="days_input" class="input-group mt-2" style="display: none;">
                            <span class="input-group-text">Number of days:</span>
                            <input type="number" id="duration_days" name="duration_days" class="form-control" value="{{ $formData['duration_days'] ?? '' }}" min="2">
                        </div>
                    </div>
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="no_guest" class="form-label fw-bold fs-5">
                        Max number of guests
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="How many guests can take part on your fishing tour max? "></i>
                    </label>
                    <input type="number" class="form-control" id="no_guest" name="no_guest" value="{{ $formData['no_guest'] ?? '' }}" placeholder="0">
                </div>

                <hr>
                
                <div class="form-group">
                    <label for="price" class="form-label fw-bold fs-5">
                        Pricing
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Do you have fixed price per tour or will the price depend on how many guests will join? Enter the gross price which will be charged to your guests."></i>
                    </label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="price_type" value="per_person" id="per_person_checkbox">
                        <label for="per_person_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">per Person</label>
                        
                        <input type="radio" name="price_type" value="per_boat" id="per_boat_checkbox">
                        <label for="per_boat_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">per Boat</label>
                    </div>
                    
                    <div class="form-group" id="dynamic-price-fields-container"></div>
                </div>

                
                <hr>
                <div class="form-group">
                    <label for="inclussions" class="form-label fw-bold fs-5">
                        Included in the price
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Chose all extras which are included in your fishing tour without additional price charges."></i>
                    </label>
                    <input type="text" class="form-control" name="inclussions" id="inclussions" data-role="tagsinput" placeholder="Select inclussions...">
                </div>

                <hr>
                <div class="form-group">
                    <label for="extra_pricing" class="form-label fw-bold fs-5">
                        Extras which can be booked additionally
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Here you can add extras which can be booked with an additional surecharge. Enter the name and the price per person and add extras by clicking the plus symbol below. Example: Lunch for 15€ per person. These extras can actively be chosen by your guests during a reservation request. "></i>
                        <button type="button" id="add-extra" class="btn btn-sm btn-secondary ms-2"><i class="fas fa-plus"></i></button>
                    </label>
                    <div id="extras-container"></div>
                </div>

                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="validateStep(currentStep)">Next</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Submit</button>
                    </div>
                </div>
            </div>

            <!-- Step 8 -->
            <div class="step" id="step7">
                <h5>Define your availability and booking options</h5>
                <div class="form-group">
                    <label for="allowed_booking_advance" class="form-label fw-bold fs-5">
                        How last minute can a guest book a tour with you?
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="How many days in advance do you allow a booking of your fishing trip? "></i>
                    </label>
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

                <hr>

                <div class="form-group">
                    <label for="booking_window" class="form-label fw-bold fs-5">
                        How far into the future can a guest book a tour with you?
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="How many months into the future do you allow the booking of your fishing tour?"></i>
                    </label>
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
                
                <hr>

                <div class="form-group">
                    <label for="seasonal_trip" class="form-label fw-bold fs-5">
                        Seasonal Trip
                        <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top" title="Is your fishing tour available the whole year or are there any restrictions such as fish protection period, ice fishing only in winter, etc.?"></i>
                    </label>
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
                                    <input type="checkbox" name="months[]" value="{{ strtolower($month) }}" id="avail_{{ strtolower($month) }}">
                                    <label for="avail_{{ strtolower($month) }}" class="btn btn-outline-primary btn-checkbox w-100">{{ $month }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                <div class="button-group">
                    <div class="left-buttons">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Leave & Save to Draft</button>
                    </div>
                    <div class="right-buttons">
                        <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Submit & Publish</button>
                    </div>
                </div>
            </div>
            <div id="loadingScreen" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 24px;">
                    Loading...
                </div>
            </div>
        </form>
    </div>
</div>
@include('pages.guidings.includes.scripts.multi-step-form-script')

