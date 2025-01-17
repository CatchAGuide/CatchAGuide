@component('mail::message')
# New Vacation Booking Request

Dear Administrator,

A new booking request has been received with the following details:

## Customer Information
- **Name:** {{ $booking->title }}. {{ $booking->name }} {{ $booking->surname }}
- **Email:** {{ $booking->email }}
- **Phone:** +{{ $booking->phone_country_code }} {{ $booking->phone }}
- **Address:** {{ $booking->street }}, {{ $booking->post_code }} {{ $booking->city }}, {{ $booking->country }}

## Booking Details
- **Booking Type:** {{ ucfirst($booking->booking_type) }}
- **Duration:** {{ $booking->duration }} days
- **Dates:** {{ date('d M Y', strtotime($booking->start_date)) }} - {{ date('d M Y', strtotime($booking->end_date)) }}
- **Number of Persons:** {{ $booking->number_of_persons }}
- **Total Price:** {{ number_format($booking->total_price, 2) }} €

@if($booking->comments)
## Additional Comments
{{ $booking->comments }}
@endif

@component('mail::button', ['url' => config('app.url').'/admin/vacationsbookings/'.$booking->id])
View Booking Details
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endcomponent