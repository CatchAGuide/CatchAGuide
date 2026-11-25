<?php

namespace App\Http\Livewire\Guide;

use App\Http\Requests\RejectionRequest;
use App\Models\Booking;
use App\Models\Guiding;
use App\Models\VoucherOrder;
use App\Presenters\Booking\GuideBookingPresenter;
use App\Presenters\Voucher\GuideVoucherOrderPresenter;
use App\Services\Guide\Area\GuideBookingActionException;
use App\Services\Guide\Area\GuideBookingService;
use App\Services\Guide\Area\GuideVoucherService;
use Illuminate\Validation\Rule;
use App\Support\GuideAreaNav;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Guide Area › Bookings (/guide/bookings): "New & open" (open requests and voucher orders
 * that still need Paid / Sent), then the Tours tab (all tour bookings with tiles, search,
 * tour and period filters) and the Vouchers tab (the voucher order list). Thin: the rules
 * live in GuideBookingService / GuideVoucherService, shared with /api/guide/*.
 */
#[Layout('layouts.guide-area')]
class Bookings extends Component
{
    use WithPagination;

    public const TABS = ['tours', 'vouchers'];

    /** Alternative dates the decline form offers (spec BK-06: up to 3). */
    public const DECLINE_DATES = 3;

    #[Url(except: 'tours')]
    public string $tab = 'tours';

    #[Url(except: 'all')]
    public string $status = GuideBookingService::FILTER_ALL;

    #[Url(except: 0)]
    public int $tour = 0;

    #[Url(except: 'all')]
    public string $period = GuideBookingService::PERIOD_ALL;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** Deep link from emails / Overview: open this booking's card (and its decline form). */
    #[Url(except: 0)]
    public int $booking = 0;

    public ?int $declining = null;

    public string $declineMessage = '';

    /** @var list<string> */
    public array $declineDates = ['', '', ''];

    public ?int $cancelling = null;

    public string $cancelReason = '';

    public function mount(GuideBookingService $bookings): void
    {
        $this->tab = in_array($this->tab, self::TABS, true) ? $this->tab : 'tours';

        if ($this->booking) {
            $this->focus($this->booking, request()->boolean('decline'), $bookings);
        }
    }

    /**
     * Shows a booking's card expanded on the page that holds it, filters reset (spec 5.6, BK-01
     * "Details"); with $decline its decline form is opened.
     */
    public function focus(int $id, bool $decline = false, ?GuideBookingService $bookings = null): void
    {
        $bookings ??= app(GuideBookingService::class);
        $found = $bookings->find(auth()->user(), $id);
        if (! $found) {
            $this->booking = 0;

            return;
        }

        $this->reset('status', 'tour', 'period', 'search');
        $this->tab = 'tours';
        $this->booking = (int) $found->id;
        $this->setPage($bookings->pageOf(auth()->user(), $found));

        if ($decline && $found->status === 'pending') {
            $this->openDecline((int) $found->id);
        }

        $this->dispatch('guide-area-scroll', target: 'booking-'.$found->id);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'tour', 'period'], true)) {
            $this->booking = 0;
            $this->resetPage();
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'tours';
        $this->booking = 0;
    }

    public function setStatus(string $status): void
    {
        // Clicking the selected tile again resets the filter (spec BK-03).
        $this->status = in_array($status, GuideBookingService::FILTERS, true) && $status !== $this->status ? $status : GuideBookingService::FILTER_ALL;
        $this->booking = 0;
        $this->resetPage();
    }

    public function accept(int $id, GuideBookingService $bookings): void
    {
        $booking = $this->ownBooking($id, $bookings);

        try {
            $bookings->accept($booking);
            $this->toast(__('guide_area.bookings.accepted'));
        } catch (GuideBookingActionException $e) {
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function openDecline(int $id): void
    {
        $this->resetErrorBag();
        $this->reset('declineMessage', 'cancelling');
        $this->declineDates = array_fill(0, self::DECLINE_DATES, '');
        $this->declining = $id;
    }

    public function closeForms(): void
    {
        $this->resetErrorBag();
        $this->reset('declining', 'cancelling', 'declineMessage', 'cancelReason');
    }

    public function decline(GuideBookingService $bookings): void
    {
        $booking = $this->ownBooking((int) $this->declining, $bookings);
        $this->declineDates = array_map('trim', array_pad($this->declineDates, self::DECLINE_DATES, ''));
        $dates = array_values(array_unique(array_filter($this->declineDates)));

        $this->validate([
            'declineMessage' => ['required', 'string', 'min:'.RejectionRequest::MIN_MESSAGE, 'max:'.RejectionRequest::MAX_MESSAGE],
            'declineDates' => [function (string $attribute, $value, \Closure $fail) use ($dates) {
                if ($dates === []) {
                    $fail(__('checkout.reject.errors.dates_required'));
                }
            }],
            'declineDates.*' => ['nullable', 'date_format:Y-m-d', 'after:today'],
        ], [
            'declineMessage.required' => __('checkout.reject.errors.message_min', ['min' => RejectionRequest::MIN_MESSAGE]),
            'declineMessage.min' => __('checkout.reject.errors.message_min', ['min' => RejectionRequest::MIN_MESSAGE]),
            'declineDates.*.after' => __('checkout.reject.errors.date_unavailable'),
            'declineDates.*.date_format' => __('checkout.reject.errors.date_unavailable'),
        ]);

        if (RejectionRequest::unavailableDates($booking, $dates) !== []) {
            $this->addError('declineDates', __('checkout.reject.errors.date_unavailable'));

            return;
        }

        try {
            $bookings->decline($booking, trim($this->declineMessage), $dates);
            $this->closeForms();
            $this->toast(__('guide_area.bookings.declined'));
        } catch (GuideBookingActionException $e) {
            $this->closeForms();
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function openCancel(int $id): void
    {
        $this->resetErrorBag();
        $this->reset('cancelReason', 'declining');
        $this->cancelling = $id;
    }

    public function cancel(GuideBookingService $bookings): void
    {
        $booking = $this->ownBooking((int) $this->cancelling, $bookings);
        $this->validate(['cancelReason' => ['required', Rule::in(GuideBookingService::CANCEL_REASONS)]], [
            'cancelReason.required' => __('guide_area.bookings.cancel_reason_required'),
        ]);

        try {
            $bookings->cancel($booking, $this->cancelReason);
            $this->closeForms();
            $this->toast(__('guide_area.bookings.cancelled'));
        } catch (GuideBookingActionException $e) {
            $this->closeForms();
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function toggleVoucherPaid(int $id, GuideVoucherService $vouchers): void
    {
        $order = $this->ownVoucher($id);
        $vouchers->mark($order, paid: ! $order->is_paid);
        $this->toast(__($order->is_paid ? 'guide_area.vouchers.marked_paid' : 'guide_area.vouchers.unmarked_paid'));
    }

    public function toggleVoucherSent(int $id, GuideVoucherService $vouchers): void
    {
        $order = $this->ownVoucher($id);
        $vouchers->mark($order, sent: ! $order->is_sent);
        $this->toast(__($order->is_sent ? 'guide_area.vouchers.marked_sent' : 'guide_area.vouchers.unmarked_sent'));
    }

    public function render(GuideBookingService $bookings, GuideVoucherService $vouchers, GuideBookingPresenter $presenter, GuideVoucherOrderPresenter $voucherPresenter)
    {
        $guide = auth()->user();
        $filters = ['status' => $this->status, 'tour' => $this->tour, 'period' => $this->period, 'search' => $this->search];

        $page = $bookings->paginate($guide, $filters);
        $page->setCollection($page->getCollection()->map(fn (Booking $b) => $presenter->present($b)));

        $openRequests = $bookings->openRequests($guide)->map(fn (Booking $b) => $presenter->present($b));
        $openVouchers = $vouchers->open($guide)->map(fn (VoucherOrder $o) => $voucherPresenter->present($o));

        return view('livewire.guide.bookings', [
            'bookings' => $page,
            'counts' => $bookings->counts($guide, $filters),
            'filters' => GuideBookingService::FILTERS,
            'periods' => GuideBookingService::PERIODS,
            'cancelReasons' => GuideBookingService::CANCEL_REASONS,
            'tours' => Guiding::query()->where('user_id', $guide->id)->orderBy('title')->get(['id', 'title']),
            'openRequests' => $openRequests,
            'openVouchers' => $openVouchers,
            'voucherCounts' => $vouchers->counts($guide),
            'minDate' => now()->addDay()->toDateString(),
        ])->title(__('guide_area.bookings.title'));
    }

    private function ownBooking(int $id, GuideBookingService $bookings): Booking
    {
        $booking = $bookings->find(auth()->user(), $id);
        abort_if($booking === null, 404);

        return $booking;
    }

    private function ownVoucher(int $id): VoucherOrder
    {
        $order = VoucherOrder::orders()->where('guide_id', auth()->id())->findOrFail($id);
        $this->authorize('update', $order);

        return $order;
    }

    private function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('guide-area-toast', message: $message, type: $type);
        $this->dispatch('guide-area-badges', badges: GuideAreaNav::badges(auth()->user()));
    }
}
