<style>
/* Camp Form Styles - Following the same pattern as accommodation and rental boat forms */
#camp-form {
    max-width: 100%;
    margin: 0 auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.step-wrapper {
    position: relative;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin-bottom: 30px;
}

.step-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}

.step-button {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.step-button.active {
    background: #fff;
    color: #667eea;
    border-color: #fff;
    transform: scale(1.1);
}

.step-button.completed {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.step-line {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    z-index: 1;
}

.step {
    display: none;
    padding: 30px;
    min-height: 500px;
}

.step.active {
    display: block;
}

.step h5 {
    color: #333;
    margin-bottom: 25px;
    font-weight: 600;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.form-label i {
    color: #6c757d;
    margin-left: 8px;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-checkbox-container {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
    position: relative;
}

.btn-checkbox-container input[type="checkbox"] {
    display: none;
}

.btn-checkbox {
    padding: 12px 20px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: #fff;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    margin-bottom: 10px;
}

.btn-checkbox:hover {
    border-color: #667eea;
    color: #667eea;
}

.btn-checkbox-container input[type="checkbox"]:checked + .btn-checkbox {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.extra-input {
    margin-top: 10px;
    display: none;
}

.btn-checkbox-container input[type="checkbox"]:checked ~ .extra-input {
    display: block;
}

.camp-facilities-grid,
.rental-requirements-grid,
.boat-information-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.button-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.left-buttons,
.right-buttons {
    display: flex;
    gap: 10px;
}

.row-button {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn {
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
}

.file-upload-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    left: -9999px;
    opacity: 0;
}

.file-upload-btn {
    display: inline-block;
    padding: 15px 30px;
    background: #667eea;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    width: 100%;
    border: 2px dashed #667eea;
}

.file-upload-btn:hover {
    background: #5a6fd8;
    border-color: #5a6fd8;
}

.image-area {
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.image-preview {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.image-preview img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.image-preview .remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(220, 53, 69, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.primary-image {
    border: 3px solid #28a745;
}

.primary-image::after {
    content: "Primary";
    position: absolute;
    top: 5px;
    left: 5px;
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 8px;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

/* Responsive Design */
@media (max-width: 768px) {
    .step-buttons {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .step-button {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .camp-facilities-grid,
    .rental-requirements-grid,
    .boat-information-grid {
        grid-template-columns: 1fr;
    }
    
    .button-group {
        flex-direction: column;
        gap: 15px;
    }
    
    .left-buttons,
    .right-buttons {
        width: 100%;
        justify-content: center;
    }
}

/* Tags Input Styling */
.bootstrap-tagsinput {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 8px 12px;
    min-height: 45px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
}

.bootstrap-tagsinput .tag {
    background: #667eea;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    margin: 2px;
    display: inline-block;
}

.bootstrap-tagsinput .tag [data-role="remove"] {
    margin-left: 5px;
    cursor: pointer;
}

.bootstrap-tagsinput input {
    border: none;
    outline: none;
    background: transparent;
    flex: 1;
    min-width: 100px;
}

/* Multi-select styling */
.select2-container--default .select2-selection--multiple {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    min-height: 45px;
    padding: 5px;
}

.select2-container--default .select2-selection--multiple:focus {
    border-color: #667eea;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: #667eea;
    border: 1px solid #667eea;
    border-radius: 4px;
    color: white;
    padding: 4px 8px;
    margin: 2px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-right: 5px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #ff6b6b;
}
</style>
