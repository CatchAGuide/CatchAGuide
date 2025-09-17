<style>
/* Step Navigation Styles */
.step-wrapper {
    position: relative;
    margin-bottom: 30px;
}

.step-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.step-button {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    border: 3px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.step-button.active {
    background: #007bff;
    border-color: #007bff;
    color: white;
}

.step-button.completed {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.step-button i {
    font-size: 18px;
}

.step-line {
    position: absolute;
    top: 25px;
    left: 25px;
    right: 25px;
    height: 3px;
    background: #e9ecef;
    z-index: 1;
}

.step-line::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: #007bff;
    transition: width 0.3s ease;
    width: 0%;
}

.step-line.step-1::after { width: 0%; }
.step-line.step-2::after { width: 20%; }
.step-line.step-3::after { width: 40%; }
.step-line.step-4::after { width: 60%; }
.step-line.step-5::after { width: 80%; }
.step-line.step-6::after { width: 100%; }

/* Step Content */
.step {
    display: none;
}

.step.active {
    display: block;
}

.step h5 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* File Upload Styles */
.file-upload-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-btn {
    display: block;
    padding: 15px 30px;
    background: #007bff;
    color: white;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    border: 2px solid #007bff;
}

.file-upload-btn:hover {
    background: #0056b3;
    border-color: #0056b3;
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
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.image-preview img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.image-preview .remove-btn {
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

.image-preview .primary-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

/* Button Groups */
.button-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.left-buttons, .right-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.row-button {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.btn-primary {
    background: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    border-color: #545b62;
}

.btn-info {
    background: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
    border-color: #138496;
}

/* Checkbox Grid */
.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 10px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.checkbox-label:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
}

.checkbox-text {
    font-weight: 500;
    color: #2c3e50;
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
    }
    
    .step-button i {
        font-size: 14px;
    }
    
    .button-group {
        flex-direction: column;
        gap: 15px;
    }
    
    .left-buttons, .right-buttons {
        width: 100%;
        justify-content: center;
    }
    
    .checkbox-grid {
        grid-template-columns: 1fr;
    }
    
    .image-area {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
}

/* Error Messages */
.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.alert {
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* Tooltips */
[data-bs-toggle="tooltip"] {
    cursor: help;
}

/* Loading States */
.btn.loading {
    position: relative;
    color: transparent;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>