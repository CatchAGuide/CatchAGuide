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

.form-check-input{
    position: relative !important;
    margin-left:0 !important;
}
</style>
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
      <div class="page-header">
          <h1 class="page-title">Create Guiding</h1>
          <div>
              <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Administration</a></li>
                  <li class="breadcrumb-item"><a href="{{ url()->previous() }}">All Guidings</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Create Guiding</li>
              </ol>
          </div>
      </div>

      <div class="container shadow-lg p-4 my-5">
        @include('pages.guidings.multi-step-form', ['is_admin_guiding_form' => true])
        {{-- @livewire('admin-edit-guiding',['guiding' => $guiding]) --}}
      </div>
    </div>
</div>
@endsection

