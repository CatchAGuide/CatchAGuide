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
</style>
@endpush
