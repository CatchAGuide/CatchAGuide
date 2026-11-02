<?php

namespace Tests\Feature\Admin;

use App\Contracts\Booking\HasDoneReasonContract;
use App\Enums\DoneReason;
use App\Models\CampVacationBooking;
use App\Models\ContactSubmission;
use App\Models\Employee;
use App\Models\ProductReport;
use App\Models\TripBooking;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * "Done" on camp/trip/contact requests says how the request ended: booked or no reply.
 */
class RequestDoneReasonAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): Employee
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');

        return $employee;
    }

    private function createCampBooking(): CampVacationBooking
    {
        return CampVacationBooking::query()->create([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => 1,
            'preferred_date' => now()->addMonth()->toDateString(),
            'number_of_persons' => 2,
            'name' => 'Done Reason Guest',
            'email' => 'done-reason-guest@example.com',
            'phone_country_code' => '+49',
            'phone' => '1700000000',
            'message' => 'Done reason test.',
            'status' => CampVacationBooking::STATUS_OPEN,
        ]);
    }

    private function createContactRequest(): ContactSubmission
    {
        return ContactSubmission::query()->create([
            'name' => 'Done Reason Contact',
            'email' => 'done-reason-contact@example.com',
            'description' => 'Question.',
            'status' => ContactSubmission::STATUS_OPEN,
        ]);
    }

    public function test_done_as_booked_is_saved_labelled_and_logged(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createCampBooking();

        $this->patchJson(route('admin.camp-vacation-bookings.update-status', $booking), [
            'status' => 'done',
            'done_reason' => DoneReason::Booked->value,
        ])
            ->assertOk()
            ->assertJson([
                'status' => 'done',
                'done_reason' => 'booked',
                'status_label' => __('message.contact_request_status.done').' · '.__('admin.done_reason.booked'),
            ]);

        $booking->refresh();
        $this->assertSame(DoneReason::Booked, $booking->doneReason());
        $this->assertSame('booked', $booking->statusChanges()->first()->reason);
    }

    public function test_switching_only_the_reason_is_a_change_and_shows_in_history(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createCampBooking();
        $url = route('admin.camp-vacation-bookings.update-status', $booking);

        $this->patchJson($url, ['status' => 'done', 'done_reason' => 'no_reply'])->assertOk();
        $this->patchJson($url, ['status' => 'done', 'done_reason' => 'no_reply'])->assertOk();
        $this->patchJson($url, ['status' => 'done', 'done_reason' => 'booked'])->assertOk();

        $this->assertSame(2, $booking->statusChanges()->count());

        $items = $this->getJson(route('admin.camp-vacation-bookings.status-history', $booking))->json('items');
        $done = __('message.contact_request_status.done');
        $this->assertSame($done.' · '.__('admin.done_reason.no_reply'), $items[0]['from_label']);
        $this->assertSame($done.' · '.__('admin.done_reason.booked'), $items[0]['to_label']);
        $this->assertSame('done-booked', $items[0]['variant']);
        $this->assertNull($items[0]['reason']);
        $this->assertSame('done-no_reply', $items[1]['variant']);
    }

    public function test_reopening_clears_the_reason(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createCampBooking();
        $url = route('admin.camp-vacation-bookings.update-status', $booking);

        $this->patchJson($url, ['status' => 'done', 'done_reason' => 'booked']);
        $this->patchJson($url, ['status' => 'in_process'])->assertJson(['done_reason' => null]);

        $this->assertNull($booking->fresh()->done_reason);
    }

    public function test_invalid_reason_is_rejected(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createCampBooking();

        $this->patchJson(route('admin.camp-vacation-bookings.update-status', $booking), ['status' => 'done', 'done_reason' => 'maybe'])
            ->assertUnprocessable();

        $this->assertSame(CampVacationBooking::STATUS_OPEN, $booking->fresh()->status);
    }

    public function test_trip_and_contact_requests_take_a_reason_too(): void
    {
        $this->actingAsEmployee();
        $contact = $this->createContactRequest();

        $this->patchJson(route('admin.contact-requests.update-status', $contact), ['status' => 'done', 'done_reason' => 'no_reply'])
            ->assertOk()
            ->assertJsonPath('done_reason', 'no_reply');
        $this->assertSame(DoneReason::NoReply, $contact->fresh()->doneReason());

        $this->assertInstanceOf(HasDoneReasonContract::class, new TripBooking);
        $this->assertNotInstanceOf(HasDoneReasonContract::class, new ProductReport);
    }

    public function test_list_offers_both_outcomes_and_shows_the_split(): void
    {
        $this->actingAsEmployee();
        $contact = $this->createContactRequest();
        $this->patchJson(route('admin.contact-requests.update-status', $contact), ['status' => 'done', 'done_reason' => 'booked']);

        $this->get(route('admin.contact-requests.index'))
            ->assertOk()
            ->assertSee('data-done-reason="booked"', false)
            ->assertSee('data-done-reason="no_reply"', false)
            ->assertSee('btn-done-booked', false)
            ->assertSee(__('admin.done_reason.heading'))
            ->assertSee('class="done-split"', false);
    }

    public function test_no_reply_requests_get_no_review_request_and_no_finance_row(): void
    {
        $this->actingAsEmployee();
        $booked = $this->createCampBooking();
        $noReply = $this->createCampBooking();
        $legacyDone = $this->createCampBooking();

        $this->patchJson(route('admin.camp-vacation-bookings.update-status', $booked), ['status' => 'done', 'done_reason' => 'booked']);
        $this->patchJson(route('admin.camp-vacation-bookings.update-status', $noReply), ['status' => 'done', 'done_reason' => 'no_reply']);
        $legacyDone->forceFill(['status' => 'done'])->save();

        $this->assertTrue($booked->fresh()->canRequestReview());
        $this->assertFalse($noReply->fresh()->canRequestReview());
        $this->assertTrue($legacyDone->fresh()->canRequestReview());

        $ids = CampVacationBooking::query()
            ->where('status', 'done')
            ->excludingNoReply()
            ->whereKey([$booked->id, $noReply->id, $legacyDone->id])
            ->pluck('id')
            ->all();
        $this->assertEqualsCanonicalizing([$booked->id, $legacyDone->id], $ids);
    }

    public function test_breakdown_counts_outcomes_and_rate(): void
    {
        $requests = collect([
            (new ContactSubmission)->forceFill(['status' => 'done', 'done_reason' => 'booked']),
            (new ContactSubmission)->forceFill(['status' => 'done', 'done_reason' => 'booked']),
            (new ContactSubmission)->forceFill(['status' => 'done', 'done_reason' => 'booked']),
            (new ContactSubmission)->forceFill(['status' => 'done', 'done_reason' => 'no_reply']),
            (new ContactSubmission)->forceFill(['status' => 'done']),
            (new ContactSubmission)->forceFill(['status' => 'open']),
        ]);

        $this->assertSame(['booked' => 3, 'no_reply' => 1, 'rate' => 75], DoneReason::breakdown($requests));
        $this->assertSame(['booked' => 0, 'no_reply' => 0, 'rate' => null], DoneReason::breakdown(collect()));
    }
}
