<?php

namespace App\Http\Livewire\Guide;

use App\Models\Booking;
use App\Models\Guiding;
use App\Models\VoucherOrder;
use App\Presenters\Booking\GuideBookingPresenter;
use App\Presenters\Guiding\GuidePublicProfilePresenter;
use App\Presenters\Voucher\GuideVoucherOrderPresenter;
use App\Services\Guide\Area\GuideBookingActionException;
use App\Services\Guide\Area\GuideBookingService;
use App\Services\Guide\Area\GuideOptimizationService;
use App\Services\Guide\Area\GuideReviewService;
use App\Services\Guide\Area\GuideVoucherService;
use App\Support\GuideAreaNav;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Guide Area › Overview (/guide): only what needs the guide's attention now — open requests
 * and voucher orders, upcoming tours, to-dos, tour status and the latest review (spec 4).
 * No statistics here (they live in Invoices & commission).
 */
#[Layout('layouts.guide-area')]
class Overview extends Component
{
    public function accept(int $id, GuideBookingService $bookings): void
    {
        $booking = $bookings->find(auth()->user(), $id);
        abort_if($booking === null, 404);

        try {
            $bookings->accept($booking);
            $this->dispatch('guide-area-toast', message: __('guide_area.bookings.accepted'));
            $this->dispatch('guide-area-badges', badges: GuideAreaNav::badges(auth()->user()));
        } catch (GuideBookingActionException $e) {
            $this->dispatch('guide-area-toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(
        GuideBookingService $bookings,
        GuideVoucherService $vouchers,
        GuideOptimizationService $optimization,
        GuideReviewService $reviews,
        GuideBookingPresenter $bookingPresenter,
        GuideVoucherOrderPresenter $voucherPresenter,
        GuidePublicProfilePresenter $profile,
    ) {
        $guide = auth()->user();
        $score = $optimization->evaluate($guide);
        $hour = (int) Carbon::now(config('guide_area.timezone', 'Europe/Berlin'))->hour;
        $latest = $reviews->latest($guide);
        $tourCounts = Guiding::query()->where('user_id', $guide->id)
            ->selectRaw('COUNT(*) AS total, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS live')
            ->toBase()->first();

        return view('livewire.guide.overview', [
            'greeting' => __('guide_area.overview.greeting.'.($hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening')), ['name' => $profile->name($guide)]),
            'today' => Carbon::now(config('guide_area.timezone', 'Europe/Berlin'))->translatedFormat('l, j F'),
            'openCount' => $bookings->openCount($guide),
            'requests' => $bookings->openRequests($guide, 3)->map(fn (Booking $b) => $bookingPresenter->present($b)),
            'vouchers' => $vouchers->open($guide, 2)->map(fn (VoucherOrder $o) => $voucherPresenter->present($o)),
            'upcoming' => $bookings->upcoming($guide, 5)->map(fn (Booking $b) => $bookingPresenter->present($b)),
            'score' => $score,
            'topTodos' => $optimization->openChecks($score, 3),
            'liveTours' => (int) ($tourCounts->live ?? 0),
            'totalTours' => (int) ($tourCounts->total ?? 0),
            'latest' => $latest ? [
                'score' => (float) $latest->overall_score,
                'guest' => $reviews->guestName($latest),
                'tour' => (string) ($latest->guiding?->title ?? ''),
                'date' => $latest->created_at?->translatedFormat('M j'),
                'comment' => trim((string) $latest->comment),
            ] : null,
        ])->title(__('guide_area.overview.title'));
    }
}
