<?php

namespace Tests\Feature\Mail;

use App\Mail\Ceo\BookingAcceptMailToCEO;
use App\Mail\Ceo\BookingRejectMailToCEO;
use App\Mail\Ceo\BookingRequestMailToCEO;
use App\Mail\Guest\BookingAcceptMail;
use App\Mail\Guest\BookingRejectMail;
use App\Mail\Guest\GuestBookingRequestMail;
use App\Mail\Guide\GuideBookingAcceptedMail;
use App\Mail\Guide\GuideBookingRequestMail;
use App\Models\Booking;
use App\Models\Guiding;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Reschedule paths pass $original->user / $booking->user straight through to these
 * mailables (see BookingService::rescheduleGuidingBooking[InPlace]); for bookings whose
 * user_id never resolved to a User/UserGuest row that relation is null. The templates
 * used to dereference $user->firstname directly, which crashed the queued mail job
 * (visible in production's failed_jobs table) so the booking notification never sent.
 * This affected not just the request emails but the accept/reject mailables too, since
 * they all resolve `user` from the same nullable $booking->user relation.
 */
class BookingRequestMailNullUserTest extends TestCase
{
    use DatabaseTransactions;

    private Booking $booking;

    private Guiding $guiding;

    private User $guide;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guide = User::factory()->create();
        $media = Media::factory()->create();
        $this->guiding = Guiding::create([
            'title' => 'Test Guiding Tour',
            'location' => 'Test Bay',
            'slug' => 'test-guiding-tour',
            'max_guests' => 4,
            'duration' => 3,
            'fishing_type_id' => 1,
            'thumbnail_id' => $media->id,
            'user_id' => $this->guide->id,
        ]);

        $this->booking = Booking::create([
            'guiding_id' => $this->guiding->id,
            'user_id' => null,
            'is_guest' => true,
            'email' => 'legacy-guest@example.com',
            'count_of_users' => 2,
            'book_date' => now()->addDays(3)->toDateString(),
            'status' => 'pending',
            'token' => 'test-booking-token',
        ]);
    }

    public function test_guest_booking_request_mail_renders_without_a_user(): void
    {
        $html = (new GuestBookingRequestMail($this->booking, null, $this->guiding, $this->guide))->render();

        $this->assertStringContainsString(__('emails.guest_name'), $html);
    }

    public function test_guide_booking_request_mail_renders_without_a_user(): void
    {
        $html = (new GuideBookingRequestMail($this->booking, null, $this->guiding, $this->guide))->render();

        $this->assertStringContainsString($this->booking->email, $html);
    }

    public function test_ceo_booking_request_mail_renders_without_a_user(): void
    {
        $html = (new BookingRequestMailToCEO($this->booking, null, $this->guiding, $this->guide))->render();

        $this->assertStringContainsString($this->booking->email, $html);
    }

    public function test_guest_accept_mail_renders_without_a_user(): void
    {
        $html = (new BookingAcceptMail($this->booking))->render();

        $this->assertStringContainsString(__('emails.guest_name'), $html);
    }

    public function test_guide_accepted_mail_renders_without_a_user(): void
    {
        $html = (new GuideBookingAcceptedMail($this->booking))->render();

        $this->assertStringContainsString($this->booking->email, $html);
    }

    public function test_ceo_accept_mail_renders_without_a_user(): void
    {
        $html = (new BookingAcceptMailToCEO($this->booking))->render();

        $this->assertStringContainsString($this->booking->email, $html);
    }

    public function test_guest_reject_mail_renders_without_a_user(): void
    {
        $html = (new BookingRejectMail($this->booking))->render();

        $this->assertStringContainsString(__('emails.guest_name'), $html);
    }

    public function test_ceo_reject_mail_renders_without_a_user(): void
    {
        $html = (new BookingRejectMailToCEO($this->booking))->render();

        $this->assertStringContainsString($this->booking->email, $html);
    }
}
