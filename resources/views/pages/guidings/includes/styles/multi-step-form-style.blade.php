@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
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
        
        .step-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .step-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 80%;
            margin: 0 auto;
            position: relative;
            gap: 10px;
        }
        
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
        
        .step-buttons .step-button.active {
            color: #e8604c;
        }
        
        .step-buttons .step-button i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .step-buttons .step-button p {
            font-size: 14px;
            margin: 0;
        }
        
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
        
        .step-buttons .step-button::before {
            content: none;
        }
        
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
            height: auto;
            aspect-ratio: 6 / 4;
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
            background-color: #fef5f3;
        }

        .btn-group-toggle input[type="radio"],
        .btn-group-toggle input[type="checkbox"] {
            display: none;
        }
        
        .btn-group-toggle .btn-checkbox {
            
            border: 2px solid #ddd; /* Inactive border */
            color: black; /* Inactive text color */
            /* border: 2px solid #e8604c;
            color: #e8604c; */
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

        .btn-group-toggle .btn-checkbox {
            color: #787780;
        }

        .btn-group-toggle .btn-checkbox.active {
            background-color: #fef5f3; /* Active background */
            color: #e8604c; /* Active text color */
            border-color: #e8604c;
        }
        
        .extra-input {
            display: none;
            margin-top: 10px;
        }
        
        .extra-input.active {
            display: block;
        }
        
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
        
        [id^="submitBtn"] {
            background-color: #1f8017;
            color: white;
            border-color: #1f8017;
            margin-top: 15px;
            flex: 0 0 auto;
            order: 2;
        }
        
        [id^="submitBtn"]:hover {
            background-color: #1f8017;
            border-color: #1f8017;
        }
        
        [id^="saveDraftBtn"] {
            background-color: #787780;
            color: white;
            border-color: #787780;
            order: 2;
            margin-left: auto;
        }

        [id^="saveDraftBtn"]:hover {
            background-color: #e8604c;
            border-color: #e8604c;
        }
        [id^="prevBtn"], [id^="nextBtn"], [id^="submitBtn"] {
            background-color: #262e35;
            color: white;
            border-color: #262e35;
            margin-top: 15px;
        }
        
        [id^="nextBtn"]:hover, [id^="submitBtn"]:hover {
            background-color: #1f8017;
            border-color: #1f8017;
        }

        [id^="prevBtn"]:hover {
            background-color: #d4c614;
            border-color: #d4c614;
        }
        .step-form-container {
            flex-grow: 1;
            width: 100%;
        }
        
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
            max-height: 300px;
            overflow-y: auto;
        }

        .btn-checkbox-container {
            flex: 1 1 calc(20% - 10px); /* 5 columns */
            margin: 5px; /* Adjust margin */
            display: flex; /* Flexbox for alignment */
            align-items: center; /* Center items vertically */
        }

        .btn-checkbox {
            width: 30%;
            text-align: left;
        }

        .extra-input {
            display: none;
            width: 70%;
        }

        .extra-input.active {
            display: block;
        }
        
        .form-group {
            margin-top: 25px;
        }

        .step-buttons .step-button.active {
            background-color: #f2856d;
            color: white;
            border-color: #f2856d;
        }

        .step-buttons .step-button {
            background-color: #313041;
            color: white;
            border-color: #313041;
        }

        .step-buttons .step-button:hover:not(.active) {
            background-color: #fef5f3;
            color: #f2856d;
            border-color: #f2856d;
        }

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
            font-size: 28px;
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

        .step-line {
            background-color: #313041;
        }

        .btn-group-toggle .btn-checkbox:hover {
            /* background-color: #fef5f3;
            color: #f2856d;
            border-color: #f2856d; */
            background-color: transparent; /* Hover background */
            color: #313041; /* Hover text color */
            border-color: #313041;
        }

        .btn-group-toggle input[type="radio"]:checked + .btn-checkbox,
        .btn-group-toggle input[type="checkbox"]:checked + .btn-checkbox {
            background-color: #fef5f3; /* Active background */
            color: #e8604c; /* Active text color */
            border-color: #e8604c;
            /* background-color: #f2856d;
            color: white;
            border-color: #f2856d; */
        }

        .card-body {
            position: relative;
            padding-top: 50px;
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
            height: 200px;
            margin: 10px;
            position: relative;
            overflow: hidden;
            border: 2px solid #313041;
            border-radius: 5px;
            display: inline-block;
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
        .image-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-wrapper.primary {
            border-color: #f2856d;
            box-shadow: 0 0 10px rgba(242, 133, 109, 0.5);
        }

        .image-preview-wrapper.primary::after {
            content: 'Title Image';
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #f2856d;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
            z-index: 1000;
        }

        .monthly-selection-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 10px;
        }

        .monthly-selection-item {
            position: relative;
            width: calc(20% - 10px);
        }

        .monthly-selection-checkbox {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .monthly-selection-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            background-color: #313041;
            color: white;
            border: 1px solid #313041;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
        }

        .monthly-selection-checkbox:checked + .monthly-selection-label {
            background-color: #f2856d;
            color: white;
            border-color: #f2856d;
        }

        .monthly-selection-label:hover {
            background-color: #fef5f3;
            color: #e8604c;
        }
        .button-group {
            /* display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px; */
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .button-group .left-buttons {
            order: 1;
        }

        .button-group .right-buttons {
            order: 2;
            display: flex;
            gap: 10px;
        }

        .button-group button {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .button-group button:last-child {
            margin-right: 0;
            margin-left: auto;
        }
        
        @media (max-width: 576px) {
            .button-group {
                flex-direction: column;
                align-items: stretch;
            }

            .button-group .left-buttons,
            .button-group .right-buttons {
                width: 100%;
                margin-bottom: 10px;
            }

            .button-group .right-buttons {
                flex-direction: column;
                gap: 10px;
            }

            [id^="saveDraftBtn"] {
                order: 1;
            }

            .right-buttons {
                order: 2;
            }
        }

        @media (max-width: 1200px) {
            .monthly-selection-item {
                width: calc(25% - 10px);
            }
        }

        @media (max-width: 992px) {
            .monthly-selection-item {
                width: calc(33.33% - 10px);
            }
        }

        @media (max-width: 768px) {
            .monthly-selection-item {
                width: calc(50% - 10px);
            }
        }

        @media (max-width: 480px) {
            .monthly-selection-item {
                width: 100%;
            }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection