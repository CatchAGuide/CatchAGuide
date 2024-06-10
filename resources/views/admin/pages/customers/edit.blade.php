@extends('admin.layouts.app')

@section('title', 'Kunde #' . $customer->id . ' editieren')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item"><a href="#">Kunden</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{route('admin.customers.update', $customer->id)}}" method="post">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label for="firstname">Vorname</label>
                                            <input type="text" class="form-control" id="firstname" name="firstname"
                                                   placeholder="Vorname" value="{{ $customer->firstname }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label for="lastname">Nachname</label>
                                            <input type="text" class="form-control" id="lastname" name="lastname"
                                                   placeholder="Nachname" value="{{ $customer->lastname }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label for="birthday">Geburtstag</label>
                                            <input type="date" max="{{ Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control" id="birthday" name="information[birthday]"
                                                   placeholder="Geburtstag" value="{{ $customer->information?->birthday?->format('Y-m-d') ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="address">Straße<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control" id="address" placeholder="Straße" required
                                                   name="information[address]" value="{{$customer->information->address ?? ''}}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="address_number">Nr.<span style="color: #e8604c">*</span></label>
                                            <input type="address_number" class="form-control" id="address_number" placeholder="Nr."
                                                   name="information[address_number]"
                                                   value="{{$customer->information->address_number ?? ''}}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="postal">PLZ<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control" id="postal" name="information[postal]" placeholder="PLZ"
                                                   value="{{$customer->information->postal ?? ''}}" required>
                                        </div>
                                        <div class="form-group col-md-8">
                                            <label for="city">Stadt<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control" id="city" name="information[city]" placeholder="Stadt"
                                                   value="{{$customer->information->city ?? ''}}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="phone">Telefonnummer<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control" id="phone" name="information[phone]" placeholder="Telefonnummer"
                                                   value="{{$customer->information->phone ?? ''}}" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="Email">Email<span style="color: #e8604c">*</span></label>
                                            <input type="text" class="form-control" id="city" name="email" placeholder="Email"
                                                   value="{{$customer->email ?? ''}}" required>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class=" btn btn-success my-1">Speichern</button>
                                        <a href="{{ route('admin.customers.index') }}" class="btn btn-danger my-1">Abbrechen</a>
                                    </div>
                                </div>

                            </form>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>
@endsection
