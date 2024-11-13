@extends('pages.profile.layouts.profile')
@section('title', isset($pageTitle) ? $pageTitle : __('profile.creategiud'))
@section('custom_style')

<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<style>

/* .guidings-gallery img{
    height:  120px !important;
    width: 200px !important;
} */
.remove-btn{
    position: absolute;
    top:0;
    right:0;
}

.button-container {
  position: relative;
  z-index: 999;
}

/* .overlay-container {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
} */

.overlay-container {
  position: fixed;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 100%;
  transform: translate(-50%, -50%);
  z-index: 999;
}

.overlay {
  position: relative;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.spinner {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 4px solid #fff;
  border-top-color: transparent;
  animation: spin 1s infinite linear;
}

.spinner-icon {
  width: 100%;
  height: 100%;
  background-image: url('path/to/spinner-icon.png');
  background-repeat: no-repeat;
  background-position: center center;
}

.message {
  margin-top: 20px;
  color: #fff;
  font-weight: bold;
  text-align: center;
}
.upload_dropZone {
  color: #0f3c4b;
  background-color: var(--colorPrimaryPale, #c8dadf);
  outline: 2px dashed var(--colorPrimaryHalf, #c1ddef);
  outline-offset: -12px;
  transition:
    outline-offset 0.2s ease-out,
    outline-color 0.3s ease-in-out,
    background-color 0.2s ease-out;
}
.upload_dropZone.highlight {
  outline-offset: -4px;
  outline-color: var(--colorPrimaryNormal, #0576bd);
  background-color: var(--colorPrimaryEighth, #c8dadf);
}
.upload_svg {
  fill: var(--colorPrimaryNormal, #0576bd);
}
.btn-upload {
  color: #fff;
  background-color: var(--colorPrimaryNormal);
}
.btn-upload:hover,
.btn-upload:focus {
  color: #fff;
  background-color: var(--colorPrimaryGlare);
}
.upload_img {
  width: calc(33.333% - (2rem / 3));
  object-fit: contain;
}
.gallery-container{
  background: rgb(237, 237, 237);
  padding:20px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

</style>
@endsection
@section('profile-content')
<div class="container shadow-lg p-4">
  {{-- @livewire('multi-step-form') --}}
  @include('pages.guidings.multi-step-form')
</div>

@endsection
{{-- 
@section('js_after')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoding"></script>
@endsection --}}
