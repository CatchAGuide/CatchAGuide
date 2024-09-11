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
            content: none; /* Remove the pseudo-element causing the white circle */
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
            margin-top: 15px;
            flex: 0 0 auto;
            margin-left: auto;
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

        /* Blue color for active checkboxes, radio buttons, and steps */
        .btn-group-toggle input[type="radio"]:checked + .btn-checkbox,
        .btn-group-toggle input[type="checkbox"]:checked + .btn-checkbox,
        .step-buttons .step-button.active {
            background-color: #f2856d;
            color: white;
            border-color: #f2856d;
        }

        /* Color for inactive steps */
        .step-buttons .step-button {
            background-color: #313041;
            color: white;
            border-color: #313041;
        }

        /* Hover effect for inactive steps */
        .step-buttons .step-button:hover:not(.active) {
            background-color: #fef5f3; /* Lighter version of #f2856d */
            color: #f2856d;
            border-color: #f2856d;
        }

        /* Round circle background for step buttons */
        .step-buttons .step-button {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
            padding: 5px;
            margin: 0 5px;
        }

        .step-buttons .step-button i {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .step-buttons .step-button p {
            font-size: 9px;
            margin: 0;
            text-align: center;
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Adjust step line color */
        .step-line {
            background-color: #313041;
        }

        /* Style for inactive checkboxes and radio buttons */
        .btn-group-toggle .btn-checkbox {
            background-color: #313041;
            color: white;
            border-color: #313041;
        }

        /* Hover effect for inactive checkboxes and radio buttons */
        .btn-group-toggle .btn-checkbox:hover {
            background-color: #fef5f3;
            color: #f2856d;
            border-color: #f2856d;
        }

        /* Style for active checkboxes and radio buttons */
        .btn-group-toggle input[type="radio"]:checked + .btn-checkbox,
        .btn-group-toggle input[type="checkbox"]:checked + .btn-checkbox {
            background-color: #f2856d;
            color: white;
            border-color: #f2856d;
        }

        /* Save to Draft button */
        #saveDraftBtn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background-color: #f2856d;
            color: white;
            border-color: #f2856d;
        }

        #saveDraftBtn:hover {
            background-color: #e8604c;
            border-color: #e8604c;
        }

        /* Adjust card position to make room for the button */
        .card-body {
            position: relative;
            padding-top: 50px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .step-buttons .step-button {
                width: 60px;
                height: 60px;
                padding: 3px;
            }

            .step-buttons .step-button i {
                font-size: 20px;
                margin-bottom: 2px;
            }

            .step-buttons .step-button p {
                font-size: 8px;
            }

            .step-line {
                top: 30px;
            }
        }

        .tooltip {
            font-size: 14px;
        }

        .tooltip-inner {
            background-color: #f2856d;
            color: white;
        }

        .bs-tooltip-auto[x-placement^=top] .arrow::before, 
        .bs-tooltip-top .arrow::before {
            border-top-color: #f2856d;
        }

        .image-preview-wrapper {
            width: 300px;
            height: 240px; /* 5:4 aspect ratio */
            margin: 10px;
            position: relative;
            overflow: hidden;
            border: 2px solid #313041;
            border-radius: 5px;
        }

        .croppable-image {
            max-width: none;
            max-height: none;
        }

        .image-controls {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .image-control-btn {
            background-color: #313041;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .image-control-btn:hover {
            background-color: #f2856d;
        }

        /* Hide overlapping parts */
        .cropper-container {
            width: 100% !important;
            height: 100% !important;
        }

        .cropper-view-box,
        .cropper-face {
            border-radius: 0;
        }

        .cropper-view-box {
            outline: none;
            box-shadow: 0 0 0 1px #39f;
        }

        .cropper-face {
            background-color: transparent;
        }

        .image-preview-wrapper.primary {
            border-color: #f2856d;
            box-shadow: 0 0 10px rgba(242, 133, 109, 0.5);
        }

        .image-preview-wrapper.primary::before {
            content: 'Primary';
            position: absolute;
            top: 5px;
            left: 5px;
            background-color: #f2856d;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
        }

        .form-group label {
            margin-bottom: 10px;
            display: block;
        }

        .option-card {
            border: 2px solid #313041;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            background-color: #313041;
            color: white;
        }
        
        .option-card:hover {
            background-color: #fef5f3;
            color: #f2856d;
            border-color: #f2856d;
        }
        
        .option-card.active {
            background-color: #f2856d;
            color: white;
            border-color: #f2856d;
        }

        .option-card .option-icon {
            font-size: 50px;
            margin-bottom: 5px;
        }

        .option-card .option-label {
            font-size: 14px;
            margin: 0;
        }

        .row.justify-content-center {
            margin-bottom: 20px;
        }
    </style>
@endsection