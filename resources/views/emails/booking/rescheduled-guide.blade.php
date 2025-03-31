@extends('emails.layouts.app')

@section('content')
<div class="email-container">
    <div class="email-header">
        <h1>Booking Rescheduled</h1>
    </div>
    
    <div class="email-body">
        <p>Hello {{ $guide->firstname }},</p>
        
        <p>A booking for your guiding service "{{ $guiding->title }}" has been rescheduled by the customer.</p>
        
        <div class="booking-details">
            <h3>Booking Details:</h3>
            <p><strong>Original Date:</strong> {{ \Carbon\Carbon::parse($originalBooking->book_date)->format('F d, Y') }}</p>
            <p><strong>New Date:</strong> {{ \Carbon\Carbon::parse($newBooking->book_date)->format('F d, Y') }}</p>
            <p><strong>Number of Guests:</strong> {{ $newBooking->count_of_users }}</p>
            <p><strong>Total Price:</strong> €{{ number_format($newBooking->price, 2) }}</p>
            <p><strong>Customer:</strong> {{ $user->firstname }} {{ $user->lastname }}</p>
            <p><strong>Customer Email:</strong> {{ $user->email }}</p>
            <p><strong>Customer Phone:</strong> {{ $newBooking->phone }}</p>
        </div>
        
        <p>Please review this rescheduled booking request and confirm or reject it by clicking one of the buttons below:</p>
        
        <div class="action-buttons">
            <a href="{{ route('booking.accept', $newBooking->token) }}" class="btn-accept">Accept Booking</a>
            <a href="{{ route('booking.reject', $newBooking->token) }}" class="btn-reject">Reject Booking</a>
        </div>
        
        <p>If you have any questions, please contact the customer directly.</p>
        
        <p>Thank you for using Catch A Guide!</p>
    </div>
    
    <div class="email-footer">
        <p>&copy; {{ date('Y') }} Catch A Guide. All rights reserved.</p>
    </div>
</div>
@endsection 