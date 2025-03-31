@extends('emails.layouts.app')

@section('content')
<div class="email-container">
    <div class="email-header">
        <h1>Booking Rescheduled</h1>
    </div>
    
    <div class="email-body">
        <p>Hello {{ $user->firstname }},</p>
        
        <p>Your booking for "{{ $guiding->title }}" with {{ $guide->firstname }} has been successfully rescheduled.</p>
        
        <div class="booking-details">
            <h3>Booking Details:</h3>
            <p><strong>Original Date:</strong> {{ \Carbon\Carbon::parse($originalBooking->book_date)->format('F d, Y') }}</p>
            <p><strong>New Date:</strong> {{ \Carbon\Carbon::parse($newBooking->book_date)->format('F d, Y') }}</p>
            <p><strong>Number of Guests:</strong> {{ $newBooking->count_of_users }}</p>
            <p><strong>Total Price:</strong> €{{ number_format($newBooking->price, 2) }}</p>
            <p><strong>Guide:</strong> {{ $guide->firstname }} {{ $guide->lastname }}</p>
            <p><strong>Guide Email:</strong> {{ $guide->email }}</p>
        </div>
        
        <p>Your booking request has been sent to the guide for confirmation. You will receive another email once the guide confirms or rejects your rescheduled booking.</p>
        
        <p>If you have any questions, please contact your guide directly.</p>
        
        <p>Thank you for using Catch A Guide!</p>
    </div>
    
    <div class="email-footer">
        <p>&copy; {{ date('Y') }} Catch A Guide. All rights reserved.</p>
    </div>
</div>
@endsection 