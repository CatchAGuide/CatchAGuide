@extends('admin.layouts.app')

@section('title', 'Create Booking')

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <style>
                /* Make checkbox checked state obvious */
                .form-check-input {
                    width: 1.15rem;
                    height: 1.15rem;
                    margin-top: 0.2rem;
                    border: 1px solid rgba(15, 23, 42, 0.25);
                    background-color: #ffffff;
                    accent-color: #4f46e5;
                }

                .form-check-input:checked {
                    background-color: #4f46e5;
                    border-color: #4f46e5;
                }

                .form-check-input:focus {
                    border-color: rgba(79, 70, 229, 0.6);
                    box-shadow: 0 0 0 0.15rem rgba(79, 70, 229, 0.18);
                }
            </style>
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Verwaltung</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Alle Buchungen</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Manual Guiding Booking</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.bookings.store') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Guiding / Trip</label>
                                        <select name="guiding_id" class="form-control @error('guiding_id') is-invalid @enderror">
                                            <option value="">Select guiding…</option>
                                            @foreach($guidings as $guiding)
                                                <option value="{{ $guiding->id }}" @selected(old('guiding_id') == $guiding->id)>
                                                    #{{ $guiding->id }} —
                                                    {{ $guiding->title }}
                                                    @if($guiding->location)
                                                        ({{ $guiding->location }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('guiding_id')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date"
                                               name="date"
                                               value="{{ old('date') }}"
                                               min="{{ now()->toDateString() }}"
                                               class="form-control @error('date') is-invalid @enderror">
                                        @error('date')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Time (optional)</label>
                                        <input type="time"
                                               name="time"
                                               value="{{ old('time') }}"
                                               class="form-control @error('time') is-invalid @enderror">
                                        @error('time')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Number of guests</label>
                                        <input type="number"
                                               name="number_of_guests"
                                               min="1"
                                               value="{{ old('number_of_guests', 1) }}"
                                               class="form-control @error('number_of_guests') is-invalid @enderror">
                                        @error('number_of_guests')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Price override (€)</label>
                                        <input type="number"
                                               step="0.01"
                                               name="price_override"
                                               value="{{ old('price_override') }}"
                                               class="form-control @error('price_override') is-invalid @enderror"
                                               placeholder="Auto if empty">
                                        @error('price_override')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Initial status</label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                                            <option value="">Pending (default)</option>
                                            <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                                            <option value="accepted" @selected(old('status') === 'accepted')>Accepted</option>
                                            <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
                                            <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                                        </select>
                                        @error('status')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <hr>

                                <h5 class="mb-3">Guest details</h5>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Guest name</label>
                                        <input type="text"
                                               name="guest_name"
                                               value="{{ old('guest_name') }}"
                                               class="form-control @error('guest_name') is-invalid @enderror">
                                        @error('guest_name')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Guest email</label>
                                        <input type="email"
                                               name="guest_email"
                                               value="{{ old('guest_email') }}"
                                               class="form-control @error('guest_email') is-invalid @enderror">
                                        @error('guest_email')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Country code</label>
                                        <input type="text"
                                               name="guest_phone_country_code"
                                               value="{{ old('guest_phone_country_code', '+49') }}"
                                               class="form-control @error('guest_phone_country_code') is-invalid @enderror">
                                        @error('guest_phone_country_code')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text"
                                               name="guest_phone"
                                               value="{{ old('guest_phone') }}"
                                               class="form-control @error('guest_phone') is-invalid @enderror">
                                        @error('guest_phone')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Internal notes / message</label>
                                        <textarea name="notes"
                                                  rows="3"
                                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <hr>

                                <div class="mb-3 form-check">
                                    <input type="hidden" name="send_emails" value="0">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="send_emails"
                                           name="send_emails"
                                           value="1"
                                           {{ old('send_emails', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_emails">
                                        Send booking request emails to guest and guide
                                    </label>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Create booking
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">How this works</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>Same flow as frontend:</strong>
                                    Uses the shared booking service to create a pending booking, blocked event, and calendar entry.
                                </li>
                                <li class="mb-2">
                                    <strong>Email simulation:</strong>
                                    If \"Send booking request emails\" is enabled, the same guest/guide/CEO emails are dispatched as in the normal checkout.
                                </li>
                                <li class="mb-2">
                                    <strong>Audit:</strong>
                                    Booking is marked as created by the current admin user with source \"admin\".
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

