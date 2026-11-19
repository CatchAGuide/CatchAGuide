<?php

namespace App\Http\Livewire\Customer;

use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Presenters\Booking\CustomerBookingPresenter;
use App\Services\Customer\CustomerBookingService;
use App\Services\Customer\CustomerFavouriteService;
use App\Support\CustomerAreaNav;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * Customer area › Overview (/profile): the next confirmed trips, requests still waiting for
 * an answer, trips to rate and a few favourites. Shown inside layouts.guide-area through
 * customer-area.page; guides land on the Guide Area overview instead.
 */
class Overview extends Component
{
    public function render(CustomerBookingService $bookings, CustomerBookingPresenter $presenter, CustomerFavouriteService $favourites)
    {
        $user = auth()->user();
        $hour = (int) Carbon::now(config('guide_area.timezone', 'Europe/Berlin'))->hour;
        $counts = $bookings->tourCounts($user);
        $waiting = $bookings->openTours($user)->map(fn (Booking $b) => $presenter->tour($b))
            ->concat($bookings->openVacationRequests($user)->map(fn ($r) => $r instanceof CampVacationBooking ? $presenter->camp($r) : $presenter->trip($r)));

        return view('livewire.customer.overview', [
            'greeting' => __('guide_area.overview.greeting.'.($hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening')), ['name' => trim((string) $user->firstname) ?: __('guide_area.customer.angler')]),
            'today' => Carbon::now(config('guide_area.timezone', 'Europe/Berlin'))->translatedFormat('l, j F'),
            'upcoming' => $bookings->upcomingTours($user)->map(fn (Booking $b) => $presenter->tour($b)),
            'upcomingCount' => $counts[CustomerBookingService::FILTER_CONFIRMED],
            'waiting' => $waiting,
            'toRate' => $bookings->toursToRate($user)->map(fn (Booking $b) => $presenter->tour($b)),
            'favourites' => $favourites->latest($user, 3),
            'favouriteCount' => $favourites->count($user),
            'tabCounts' => $bookings->tabCounts($user),
            'becomeGuideUrl' => CustomerAreaNav::becomeGuideUrl($user),
        ]);
    }
}
