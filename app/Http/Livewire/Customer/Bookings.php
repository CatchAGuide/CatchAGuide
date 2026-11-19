<?php

namespace App\Http\Livewire\Customer;

use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\VoucherOrder;
use App\Presenters\Booking\CustomerBookingPresenter;
use App\Services\Customer\CustomerBookingService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Customer area › My bookings (/profile/bookings, and /profile/my-bookings for guides):
 * tour bookings with status tiles, then camp requests, trip requests and bought vouchers in
 * their own tabs. Read-only, like the old page: the guest answers a declined request through
 * the reschedule link and rates a finished trip through the rating page.
 */
class Bookings extends Component
{
    use WithPagination;

    #[Url(except: CustomerBookingService::TAB_TOURS)]
    public string $tab = CustomerBookingService::TAB_TOURS;

    #[Url(except: CustomerBookingService::FILTER_ALL)]
    public string $status = CustomerBookingService::FILTER_ALL;

    /** Deep link ?booking=ID (old /profile/showbooking/{id}): opens that card. */
    #[Url(except: 0)]
    public int $booking = 0;

    public function mount(CustomerBookingService $bookings): void
    {
        $this->normalize();

        if ($this->booking > 0) {
            if ($bookings->findTour(auth()->user(), $this->booking) === null) {
                $this->booking = 0;

                return;
            }

            $this->tab = CustomerBookingService::TAB_TOURS;
            $this->status = CustomerBookingService::FILTER_ALL;
            $this->setPage($bookings->tourPage(auth()->user(), $this->booking));
            $this->dispatch('guide-area-scroll', target: 'booking-'.$this->booking);
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->booking = 0;
        $this->normalize();
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->booking = 0;
        $this->normalize();
        $this->resetPage();
    }

    public function render(CustomerBookingService $bookings, CustomerBookingPresenter $presenter)
    {
        $user = auth()->user();

        $page = match ($this->tab) {
            CustomerBookingService::TAB_CAMPS => $bookings->paginateCamps($user),
            CustomerBookingService::TAB_TRIPS => $bookings->paginateTrips($user),
            CustomerBookingService::TAB_VOUCHERS => $bookings->paginateVouchers($user),
            default => $bookings->paginateTours($user, $this->status),
        };

        $page->setCollection($page->getCollection()->map(fn ($row) => match (true) {
            $row instanceof Booking => $presenter->tour($row),
            $row instanceof CampVacationBooking => $presenter->camp($row),
            $row instanceof TripBooking => $presenter->trip($row),
            $row instanceof VoucherOrder => $presenter->voucher($row),
        }));

        return view('livewire.customer.bookings', [
            'items' => $page,
            'tabs' => CustomerBookingService::TABS,
            'tabCounts' => $bookings->tabCounts($user),
            'filters' => CustomerBookingService::FILTERS,
            'counts' => $this->tab === CustomerBookingService::TAB_TOURS ? $bookings->tourCounts($user) : [],
        ]);
    }

    private function normalize(): void
    {
        if (! in_array($this->tab, CustomerBookingService::TABS, true)) {
            $this->tab = CustomerBookingService::TAB_TOURS;
        }
        if (! in_array($this->status, CustomerBookingService::FILTERS, true)) {
            $this->status = CustomerBookingService::FILTER_ALL;
        }
    }
}
