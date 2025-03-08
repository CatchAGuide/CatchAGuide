@extends('admin.layouts.app')
@section('custom_style')
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

.form-check-input{
    position: relative !important;
    margin-left:0 !important;
}
.rounded{
  border-radius: 1% !important;
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
@section('title', 'Guiding #' . $formData['id'] . ' editieren')

@section('content')
<div class="container shadow-lg p-4 my-5">
    @include('pages.guidings.multi-step-form')
    {{-- @livewire('admin-edit-guiding',['guiding' => $guiding]) --}}
</div>
@endsection

@section('js_after')
@endsection
