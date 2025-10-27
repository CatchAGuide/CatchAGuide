@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
<style>
.btn-checkbox-container {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
    position: relative;
}

.btn-checkbox-container input[type="checkbox"],
.btn-checkbox-container input[type="radio"] {
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
    display: block;
}

.btn-checkbox:hover {
    border-color: #667eea;
    color: #667eea;
}

.btn-checkbox.active,
.btn-checkbox-container input[type="checkbox"]:checked + .btn-checkbox,
.btn-checkbox-container input[type="radio"]:checked + .btn-checkbox {
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

.accommodation-details-grid,
.room-configuration-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.d-flex.flex-wrap.btn-group-toggle {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}

.d-flex.flex-wrap.btn-group-toggle .btn-checkbox-container {
    margin-bottom: 0;
}

/* Per-Person Pricing Styles */
#per-person-pricing-container {
    margin-top: 15px;
}

.per-person-pricing-row {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.per-person-pricing-row .card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.3s ease;
}

.per-person-pricing-row .card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.per-person-pricing-row .card-body {
    padding: 20px;
}

/* Person Count Badge Display */
.person-count-display {
    text-align: center;
}

.person-count-badge {
    display: inline-block;
    font-size: 1.2rem;
    padding: 10px 15px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.person-count-display small {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 5px;
}

.per-person-pricing-row .form-label {
    font-size: 0.9rem;
    color: #495057;
    margin-bottom: 5px;
}

.per-person-pricing-row .form-control {
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.per-person-pricing-row .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.per-person-pricing-row .input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-radius: 6px 0 0 6px;
}

.per-person-pricing-row .remove-pricing-row {
    width: 100%;
    padding: 10px;
}

.per-person-pricing-row small.text-muted {
    font-size: 0.75rem;
    display: block;
    margin-top: 4px;
}

#add-person-pricing-btn {
    border-radius: 6px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

#add-person-pricing-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

/* Responsive adjustments for per-person pricing */
@media (max-width: 768px) {
    .per-person-pricing-row .row > div {
        margin-bottom: 15px;
    }
    
    .per-person-pricing-row .card-body {
        padding: 15px;
    }
    
    .person-count-badge {
        font-size: 1rem;
        padding: 8px 12px;
    }
}
</style>
@endpush
