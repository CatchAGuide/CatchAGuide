@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
@endpush

@section('css_after')
    <style>
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        
        h5 {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: bold;
        }
        
        h5::after {
            content: "";
            display: block;
            width: 100%;
            height: 2px;
            background-color: #e8604c;
            position: absolute;
            bottom: 0;
            left: 0;
        }
        
        /* Container for step buttons and steps */
        .step-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        /* Container for the step buttons */
        .step-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 80%;
            margin: 0 auto;
            position: relative;
            gap: 10px;
        }
        
        /* Step buttons style (Icon and Text Only) */
        .step-buttons .step-button {
            color: #787780;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 5px;
            z-index: 2;
        }
        
        /* Active step button */
        .step-buttons .step-button.active {
            color: #e8604c; /* Red color for active steps */
        }
        
        /* Icon and text alignment */
        .step-buttons .step-button i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .step-buttons .step-button p {
            font-size: 14px; /* Adjust text size */
            margin: 0;
        }
        
        /* Line between steps */
        .step-line {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 10%;
            right: 10%;
            height: 4px;
            background-color: #ddd;
            z-index: 1;
        }
        
        /* Invisible circle effect to cut the line between each step */
        .step-buttons .step-button::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background-color: white;
            border-radius: 50%;
            z-index: 1;
        }
        
        /* Styles for the image preview */
        .image-area {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 15px;
        }
        
        .image-card {
            position: relative;
            width: 180px;
            margin: 10px;
            overflow: visible;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .image-card img {
            width: 100%;
            height: auto; /* Ensure the entire image is visible */
            object-fit: contain;
            border-radius: 10px 10px 0 0;
        }
        
        .primary-label {
            position: absolute;
            top: 5px;
            left: 5px;
            background-color: #f2856d;
            color: white;
            padding: 3px 8px;
            border-radius: 5px;
        }
        
        .btn.set-primary-btn {
            background-color: #f2856d;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 5px 10px;
            margin: 10px auto 0 auto;
            display: block;
            width: 100%;
            text-align: center;
        }
        
        .delete-image-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #e8604c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        
        /* Styles for the file input and buttons */
        .file-upload-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .file-upload-wrapper input {
            display: none;
        }
        
        .file-upload-btn {
            background-color: #f2856d;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 30px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .file-upload-btn:hover {
            background-color: #e8604c;
        }
        
        .option-card {
            border: 2px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .option-card.active {
            border-color: #e8604c;
            background-color: #fef5f3;
        }
        
        .option-card:hover {
            border-color: #e8604c;
        }

        .btn-group-toggle input[type="radio"],
        .btn-group-toggle input[type="checkbox"] {
            display: none;
        }
        
        .btn-group-toggle .btn-checkbox {
            border: 2px solid #e8604c; /* Set your desired border color */
            color: #e8604c;
            background-color: transparent;
            border-radius: 10px;
            padding: 10px 20px;
            transition: all 0.3s;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;    
        }

        .btn-group-toggle input[type="radio"]:checked + .btn-checkbox,
        .btn-group-toggle input[type="checkbox"]:checked + .btn-checkbox {
            background-color: #fef5f3;
            color: #e8604c;
            border-color: #e8604c;
        }
        
        .btn-group-toggle .btn-checkbox:hover {
            background-color: #fef5f3;
            color: #e8604c;
            border-color: #e8604c;
        }

        .btn-group-toggle .btn-checkbox {
            color: #787780; /* Match the text color of the page */
        }

        .btn-group-toggle .btn-checkbox.active {
            color: #e8604c; /* Match the active text color */
            background-color: #fef5f3; /* Match the active background color */
            border-color: #e8604c; /* Match the active border color */
        }
        
        .extra-input {
            display: none;
            margin-top: 10px;
        }
        
        .extra-input.active {
            display: block;
        }
        
        /* Centering the icon inside the card */
        .option-icon {
            font-size: 40px;
            color: #e8604c;
        }
        
        .option-label {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            color: #787780;
        }
        
        #submitBtn {
            background-color: #f2856d;
            color: white;
            border-color: #f2856d;
        }
        
        #submitBtn:hover {
            background-color: #e8604c;
            border-color: #e8604c;
        }
        
        #prevBtn, #nextBtn {
            background-color: #787780;
            color: white;
            border-color: #787780;
            margin-top: 15px;
        }
        
        #prevBtn:hover, #nextBtn:hover {
            background-color: #e8604c;
            border-color: #e8604c;
        }
        
        /* Full-width form container for all screen sizes */
        .step-form-container {
            flex-grow: 1;
            width: 100%;
        }
        
        /* Make the card and container fluid for desktop */
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }

        .bootstrap-tagsinput {
            width: 100%;
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #fff;
            color: #000;
            display: block;
            min-height: 40px;
            font-size: 16px;
        }

        .bootstrap-tagsinput .tag {
            margin-right: 5px;
            background-color: #e8604c;
            color: white;
            border-radius: 3px;
            padding: 5px;
            font-weight: bold;
        }

        .bootstrap-tagsinput input {
            border: none;
            outline: none;
            width: auto;
            max-width: 100%;
            color: #000;
        }

        .bootstrap-tagsinput .tag [data-role="remove"] {
            margin-left: 8px;
            cursor: pointer;
        }

        .dropdown-menu {
            max-height: 300px; /* Limit the height */
            overflow-y: auto;  /* Enable scrolling */
        }

        /* Adjust for larger screen sizes */
        @media (min-width: 768px) {
            .step-buttons .step-button i {
                font-size: 30px; /* Larger icon size for bigger screens */
            }
        
            .step-buttons {
                gap: 30px; /* Increased gap on larger screens */
            }
        
            .btn-group-toggle .btn {
                flex-basis: calc(50% - 20px); /* Two buttons per row */
            }
        }
        
        @media (max-width: 480px) {
            .btn-group-toggle .btn {
                flex-basis: 100%; /* Full-width buttons */
            }
        }

        /* Styles for the checkbox container and input alignment */
        .btn-checkbox-container {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .btn-checkbox {
            width: 30%; /* Checkbox column width */
            text-align: left; /* Align text to the left */
        }

        .extra-input {
            display: none;
            width: 70%; /* Input box column width */
        }

        .extra-input.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
    </style>
@endsection
<div class="card">
    <div class="card-body">
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

            <!-- Step 1 -->
            <div class="step active" id="step1">
                <h5>Basic Information</h5>

                <label for="title_image">Galery Images</label>
                <div class="file-upload-wrapper">
                    <input id="title_image" name="title_image[]" type="file" multiple onchange="previewImages(this);" />
                    <label for="title_image" class="file-upload-btn">Choose Files</label>
                    <div id="croppedImagesContainer"></div>
                </div>

                <div class="image-area" id="imagePreview"></div>
                <input type="hidden" name="primaryImage" id="primaryImageInput">

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="search" class="form-control" id="location" name="location" placeholder="Enter city or country">
                </div>

                <div class="form-group">
                    <label for="titel">Title</label>
                    <input type="text" class="form-control" id="titel" name="titel">
                </div>

                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>

            <!-- Step 2 -->
            <div class="step" id="step2">
                <h5>Guiding Information</h5>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="option-card" id="boatOption" onclick="selectOption('boat')">
                            <i class="fas fa-ship option-icon"></i>
                            <p class="option-label">Boat</p>
                            <input type="radio" name="type_of_fishing" value="boat" class="d-none">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="option-card" id="shoreOption" onclick="selectOption('shore')">
                            <i class="fas fa-water option-icon"></i>
                            <p class="option-label">Shore</p>
                            <input type="radio" name="type_of_fishing" value="shore" class="d-none">
                        </div>
                    </div>
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
                        <label for="type_of_boat">Description</label>
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
                        <label for="extras">Extras</label>
                        <input  class="form-control" name="extras" id="extras" placeholder="Add extras..." required>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>

            <!-- Step 3 -->
            <div class="step" id="step3">
                <h5>Fish Details</h5>
                
                <div class="form-group">
                    <label for="target_fish">Target Fish</label>
                    <input  class="form-control" name="target_fish" id="target_fish" placeholder="Add Target Fish..." required>
                </div>
                
                <div class="form-group">
                    <label for="methods">Methods</label>
                    <input  class="form-control" name="methods" id="methods" placeholder="Select Methods..." required>
                </div>
                
                <div class="form-group">
                    <label for="water_types">Water Types</label>
                    <input  class="form-control" name="water_types" id="water_types" placeholder="Select Water Tyles..." required>
                </div>

                <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>

            <!-- Step 4 -->
            <div class="step" id="step4">
                <h5>Expertise</h5>
                
                <div class="form-group">
                    <label for="experience_level">Experience Level</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="experience_level" value="beginner" id="beginner">
                        <label for="beginner" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Beginner</label>
                        
                        <input type="radio" name="experience_level" value="advance" id="advance">
                        <label for="advance" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Advance</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="inclussions">Inclussions</label>
                    <input  class="form-control" name="inclussions" id="inclussions" placeholder="Select inclussions..." required>
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

                <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>

            <!-- Step 5 -->
            <div class="step" id="step5">
                <h5>Long Description</h5>
                
                <div class="form-group">
                    <label for="course_of_action">What Does the Course of actions look like?</label>
                    <textarea name="course_of_action" id="course_of_action" class="form-control" placeholder="course of action. . . ."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="meeting_point">Where will be the meeting point?</label>
                    <textarea name="meeting_point" id="meeting_point" class="form-control" placeholder="meeting point. . . ."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="special_about">What is special about the waters you fish in and the fish a guest can catch with you?</label>
                    <textarea name="special_about" id="special_about" class="form-control" placeholder="specialty. . . ."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="tour_unique">What makes your tour unique?</label>
                    <textarea name="tour_unique" id="tour_unique" class="form-control" placeholder="uniqueness. . . ."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="starting_time">When is typically the starting time of your tour (morning / evening)?</label>
                    <textarea name="starting_time" id="starting_time" class="form-control" placeholder="startime time. . . ."></textarea>
                </div>

                <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>

            <!-- Step 6 -->
            <div class="step" id="step6">
                <h5>Other Information</h5>

                <div class="form-group">
                    <label for="other_information">Other Information</label>
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
                    <label for="requiements_taking_part">Requirements for taking part</label>
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
                    <label for="recommended_preparation">Recommended Preparation</label>
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

                <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>

            </div>

            <!-- Step 7 -->
            <div class="step" id="step7">
                <h5>Pricing</h5>
                <div class="form-group">
                    <label for="tour_type">Tour Type</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="tour_type" value="private" id="private">
                        <label for="private" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Private</label>
                        
                        <input type="radio" name="tour_type" value="shared" id="shared">
                        <label for="shared" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Shared</label>
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
                </div>

                <div class="form-group">
                    <label for="no_guest">Number of guest</label>
                    <input type="number" class="form-control" id="no_guest" name="no_guest" placeholder="0">
                </div>
                
                <div class="form-group">
                    <label for="price">Tour Type / Pricing</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="price" value="per_person" id="per_person_checkbox">
                        <label for="per_person_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">per Person</label>
                        
                        <input type="radio" name="price" value="per_boat" id="per_boat_checkbox">
                        <label for="per_boat_checkbox" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">per Boat</label>
                    </div>
                    
                    <div class="form-group" id="dynamic-price-fields-container"></div>
                </div>
                
                <div class="form-group">
                    <label for="extra_pricing">Extras</label>
                    <input type="number" class="form-control" id="extra_pricing" name="extra_pricing" placeholder="0">
                </div>

                <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>

            <!-- Step 8 -->
            <div class="step" id="step8">
                <h5>Schedules</h5>
                <div class="form-group">
                    <label for="allowed_booking_advance">Allowance of min. booking days in advance</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="allowed_booking_advance" value="same_day" id="same_day">
                        <label for="same_day" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Same Day</label>
                        
                        <input type="radio" name="allowed_booking_advance" value="three_days" id="three_days">
                        <label for="three_days" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Three Days Upfront</label>
                        
                        <input type="radio" name="allowed_booking_advance" value="one_week" id="one_week">
                        <label for="one_week" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">One Week</label>
                        
                        <input type="radio" name="allowed_booking_advance" value="one_month" id="one_month">
                        <label for="one_month" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">One Month</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="booking_window">Booking Window</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="booking_window" value="none" id="none">
                        <label for="none" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">None</label>
                        
                        <input type="radio" name="booking_window" value="six_months" id="six_months">
                        <label for="six_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Six(6) Months</label>
                        
                        <input type="radio" name="booking_window" value="nine_months" id="nine_months">
                        <label for="nine_months" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Nine(9) Months</label>
                        
                        <input type="radio" name="booking_window" value="one_year" id="one_year">
                        <label for="one_year" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Twelve(1 Year) Months</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="seasonal_trip">Seasonal Trip</label>
                    <div class="d-flex flex-wrap btn-group-toggle">
                        <input type="radio" name="seasonal_trip" value="season_year" id="season_year">
                        <label for="season_year" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">All Year</label>
                        
                        <input type="radio" name="seasonal_trip" value="season_monthly" id="season_monthly">
                        <label for="season_monthly" class="btn btn-outline-primary m-2 flex-fill btn-checkbox" style="flex-basis: calc(33.33% - 20px);">Monthly</label>
                    </div>
                    <div id="monthly_selection" style="display: none;">
                        <label for="months">Select Months</label>
                        <input type="text" id="months" name="months" class="form-control" placeholder="Select months">
                    </div>
                </div>

                <button type="button" class="btn btn-primary" id="prevBtn">Previous</button>
                <button type="submit" class="btn btn-success" id="submitBtn">Submit</button>
            </div>

        </form>
    </div>
</div>

@push('js_push')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="https://cdn.ckeditor.com/4.22.0/standard/ckeditor.js"></script>

<script>
    let croppers = {}; // Object to store Cropper instances by image index
    let croppedImages = []; // Array to store cropped images as base64 strings
    let primaryImageIndex = null; // Store the index of the primary image
    let autocomplete;
    
    function initialize() {
        autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('location'),
            {
                types: ['(regions)']
            }
        );

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            let city = '';
            let country = '';
            let postal_code = '';

            place.address_components.forEach(component => {
                const types = component.types;

                if (types.includes('locality')) {
                    city = component.long_name; // This is the city name
                } else if (types.includes('country')) {
                    country = component.long_name; // This is the country name
                } else if (types.includes('postal_code')) {
                    postal_code = component.long_name; // This is the postal code
                }
            });
        });
    }
    
    $(document).ready(function() {
        initialize();
        
        var extrasInput = document.querySelector('input[name=extras]');
        var extraTags = new Tagify(extrasInput, {
            whitelist: [
                'GPS', 'Echolot', 'Live Scope', 'Radar', 'Funk', 'Flybridge', 'WC', 
                'Roofing', 'Dusche', 'Küche', 'Bett', 'Wifi', 'Ice box/ Kühlschrank', 
                'Air conditioning', 'Fighting chair', 'E-Motor', 'Felitiertisch'
            ],
            maxTags: 10, // Maximum number of tags
            dropdown: {
                maxItems: 20,           // Maximum items to show in the suggestions dropdown
                classname: "tagify__dropdown", // Class name for styling
                enabled: 0,             // Always show the dropdown
                closeOnSelect: false    // Keep dropdown open after selecting a suggestion
            }
        });

        targetFishList = {!! json_encode($targets->toArray()) !!};

        var targetFishInput = document.querySelector('input[name=target_fish]');
        var targetFishTags = new Tagify(targetFishInput, {
            whitelist: targetFishList,
            maxTags: 10, // Maximum number of tags
            dropdown: {
                maxItems: 20,           // Maximum items to show in the suggestions dropdown
                classname: "tagify__dropdown", // Class name for styling
                enabled: 0,             // Always show the dropdown
                closeOnSelect: false    // Keep dropdown open after selecting a suggestion
            }
        });

        methodsList = {!! json_encode($methods->toArray()) !!};

        var methodsListInput = document.querySelector('input[name=methods]');
        var methodsListTags = new Tagify(methodsListInput, {
            whitelist: methodsList,
            maxTags: 10, // Maximum number of tags
            dropdown: {
                maxItems: 20,           // Maximum items to show in the suggestions dropdown
                classname: "tagify__dropdown", // Class name for styling
                enabled: 0,             // Always show the dropdown
                closeOnSelect: false    // Keep dropdown open after selecting a suggestion
            }
        });

        waterTypesList = {!! json_encode($waters->toArray()) !!};

        var waterTypesListInput = document.querySelector('input[name=water_types]');
        var waterTypesListTags = new Tagify(waterTypesListInput, {
            whitelist: waterTypesList,
            maxTags: 10,
            dropdown: {
                maxItems: 20,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        inclussionList = {!! json_encode($inclussions->toArray()) !!};

        var inclussionListInput = document.querySelector('input[name=inclussions]');
        var inclussionListTags = new Tagify(inclussionListInput, {
            whitelist: inclussionList,
            maxTags: 10,
            dropdown: {
                maxItems: 20,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        $('input[name="seasonal_trip"]').change(function() {
            if ($(this).val() === 'season_monthly') {
                $('#monthly_selection').show();
            } else {
                $('#monthly_selection').hide();
            }
        });

        var monthsInput = document.querySelector('input[name=season_monthly]');
        var monthsTags = new Tagify(monthsInput, {
            whitelist: [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],
            dropdown: {
                maxItems: 12, // All 12 months
                enabled: 0,   // Show suggestions on focus
                closeOnSelect: false // Keep dropdown open after selecting a month
            }
        });

        var currentStep = 1;
        var totalSteps = $('.step').length;

        $('input[type="checkbox"]').on('change', function() {
            const extraInput = $(this).closest('.btn-checkbox-container').find('.extra-input');
            
            if ($(this).is(':checked')) {
                extraInput.addClass('active');
            } else {
                extraInput.removeClass('active');
            }
        });

        $('input[name="price"]').change(function () {
            const selectedPriceOption = $(this).val();
            const dynamicFieldsContainer = $('#dynamic-price-fields-container');

            dynamicFieldsContainer.empty(); // Clear any existing fields

            if (selectedPriceOption === 'per_person') {
                const addButton = $('<button>')
                    .attr('type', 'button')
                    .addClass('btn btn-outline-primary mb-3 d-flex align-items-center')
                    .attr('data-bs-toggle', 'tooltip')
                    .attr('data-bs-placement', 'right')
                    .attr('title', 'Add Person')
                    .html('<i class="fas fa-plus"></i>')
                    .on('click', function () {
                        checkAndAddPersonField();
                    });

                dynamicFieldsContainer.append(addButton);
                $('[data-bs-toggle="tooltip"]').tooltip();

                checkAndAddPersonField();
            } else if (selectedPriceOption === 'per_boat') {
                const boatPriceField = $('<div>')
                    .addClass('form-group row align-items-center')
                    .append('<label for="boat_price" class="col-sm-3 col-form-label">Total Price</label>')
                    .append('<div class="col-sm-9"><input type="number" class="form-control" name="boat_price" placeholder="Enter total price"></div>');

                dynamicFieldsContainer.append(boatPriceField);
            }
        });

        function checkAndAddPersonField() {
            const noGuest = $('#no_guest').val();
            const personFieldCount = $('.person-price-field').length;

            $('#guest-warning').remove();

            if (noGuest === '' || parseInt(noGuest) === 0) {
                const warning = $('<p>')
                    .attr('id', 'guest-warning')
                    .addClass('text-danger')
                    .text('Please enter the number of guests before adding person price fields.');
                $('#dynamic-price-fields-container').prepend(warning);
            } else if (personFieldCount < parseInt(noGuest)) {
                addPersonField(personFieldCount + 1);
            } else {
                const warning = $('<p>')
                    .attr('id', 'guest-warning')
                    .addClass('text-danger')
                    .text('The number of persons cannot exceed the number of guests.');
                $('#dynamic-price-fields-container').prepend(warning);
            }
        }

        function addPersonField(personNumber) {
            const labelText = personNumber === 1 ? 'Price for 1 person' : `Price for ${personNumber} persons`;

            const personField = $('<div>')
                .addClass('form-group row align-items-center person-price-field mb-3')
                .append(`<label for="person_price_${personNumber}" class="col-sm-3 col-form-label">${labelText}</label>`)
                .append(
                    `<div class="col-sm-7">
                        <input type="number" class="form-control" name="person_price_${personNumber}" placeholder="Enter price for ${personNumber} person${personNumber > 1 ? 's' : ''}">
                    </div>`
                )
                .append(
                    `<div class="col-sm-2">
                        <button type="button" class="btn btn-outline-danger remove-person-field">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>`
                );

            $('#dynamic-price-fields-container').append(personField);

            personField.find('.remove-person-field').on('click', function () {
                $(this).closest('.person-price-field').remove();
                updatePersonFieldLabels();
            });
        }

        function updatePersonFieldLabels() {
            $('.person-price-field').each(function (index) {
                const personNumber = index + 1;
                const labelText = personNumber === 1 ? 'Price for 1 person' : `Price for ${personNumber} persons`;
                $(this).find('label').text(labelText);
                $(this).find('input').attr('name', `person_price_${personNumber}`);
                $(this).find('input').attr('placeholder', `Enter price for ${personNumber} person${personNumber > 1 ? 's' : ''}`);
            });
        }

        function showStep(step) {
            $('.step').removeClass('active');
            $('#step' + step).addClass('active');
            
            $('.step-button').removeClass('active');
            $('.step-button[data-step="' + step + '"]').addClass('active');

            if (step === 1) {
                $('#prevBtn').hide();
            } else {
                $('#prevBtn').show();
            }

            if (step === totalSteps) {
                $('#nextBtn').hide();
                $('#submitBtn').show();
            } else {
                $('#nextBtn').show();
                $('#submitBtn').hide();
            }
        }

        if (currentStep === 1) {
            $('#prevBtn').hide();
        }

        $('#submitBtn').hide();

        $(document).on('click', '#nextBtn', function() {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        });

        $(document).on('click', '#prevBtn', function() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        $('.step-button').click(function () {
            var step = $(this).data('step');
            currentStep = step;
            showStep(step);
        });

        showStep(currentStep);
    });

    function previewImages(input) {
        var imagePreviewContainer = $('#imagePreview');
        imagePreviewContainer.html("");

        if (input.files) {
            $.each(input.files, function(index, file) {
                if (!file.type.match('image.*')) {
                    alert('Only images are allowed!');
                    return;
                }

                var reader = new FileReader();
                reader.onload = function(e) {
                    var imgContainer = $('<div>').addClass('card image-card').attr('data-image-index', index);
                    var img = $('<img>').attr('src', e.target.result).addClass('croppable-image');
                    var deleteBtn = $('<button>').addClass('delete-image-btn').html('&times;');
                    var primaryBtn = $('<button>').addClass('btn btn-primary btn-sm set-primary-btn').text('Set as Primary');

                    primaryBtn.on('click', function() {
                        setPrimaryImage(index, img.attr('src'));
                    });

                    deleteBtn.on('click', function() {
                        removeImage(index, imgContainer);
                    });

                    imgContainer.append(img).append(deleteBtn).append(primaryBtn);
                    imagePreviewContainer.append(imgContainer);

                    img.on('load', function() {
                        croppers[index] = new Cropper(this, {
                            aspectRatio: 5 / 4,
                            viewMode: 1,
                            autoCropArea: 1,
                            movable: true,
                            zoomable: true,
                            rotatable: true,
                            scalable: true,
                        });
                    });
                };
                reader.readAsDataURL(file);
            });
        }
    }

    function setPrimaryImage(index, imageUrl) {
        event.preventDefault();

        $('.image-card').removeClass('primary-image').find('.primary-label').remove();

        let selectedImageContainer = $('.image-card[data-image-index="' + index + '"]');
        selectedImageContainer.addClass('primary-image');

        let primaryLabel = $('<span>').addClass('primary-label').text('Primary');
        selectedImageContainer.append(primaryLabel);

        primaryImageIndex = index;

        $('#primaryImageInput').val(imageUrl);
    }

    function removeImage(index, imgContainer) {
        if (croppers[index]) {
            croppers[index].destroy();
            delete croppers[index];
        }

        imgContainer.remove();

        if ($('#imagePreview').children().length === 0) {
            primaryImageIndex = null;
            $('#primaryImageInput').val(""); // Reset the primary image input
        }
    }

    function selectOption(option) {
        $('#boatOption, #shoreOption').removeClass('active');

        if (option === 'boat') {
            $('#boatOption').addClass('active');
            $('input[name="type_of_fishing"][value="boat"]').prop('checked', true);
            $('#extraFields').slideDown();  // Show extra fields for boat
            $('#nextBtn').hide(); // Hide Next button
        } else if (option === 'shore') {
            $('#shoreOption').addClass('active');
            $('input[name="type_of_fishing"][value="shore"]').prop('checked', true);
            $('#extraFields').slideUp();  // Hide extra fields for boat
            $('#nextBtn').trigger('click'); // Automatically move to next step
        }
    }

    $(document).on('submit', 'form', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Clear out previous cropped images in case the form is being submitted again
        croppedImages = [];
        $('#croppedImagesContainer').html(""); // Clear out previous hidden inputs

        // Iterate over the croppers and get cropped images as base64 strings
        Object.keys(croppers).forEach(function(index) {
            let cropper = croppers[index];
            let croppedCanvas = cropper.getCroppedCanvas();
            let croppedImageDataURL = croppedCanvas.toDataURL('image/jpeg'); // Get the cropped image as base64

            // Create a hidden input to store the base64 image data
            let hiddenInput = $('<input>').attr({
                type: 'hidden',
                name: 'croppedImages[]',
                value: croppedImageDataURL
            });

            // Append hidden input to a container in the form
            $('#croppedImagesContainer').append(hiddenInput);

            // Push base64 image data to the array (optional if needed elsewhere)
            croppedImages.push(croppedImageDataURL);

            if (index === primaryImageIndex) {
                $('#primaryImageInput').val(croppedImageDataURL);
            }
        });

        // Submit the form after adding all the cropped images
        this.submit();
    });
</script>
@endpush