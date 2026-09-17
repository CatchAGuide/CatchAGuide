<?php

namespace Tests\Unit\Services\Booking;

use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Booking\QueuedBookingJobPurger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueuedBookingJobPurgerTest extends TestCase
{
    use DatabaseTransactions;

    private function createBooking(): Booking
    {
        $guide = User::factory()->create();

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Test Tour '.uniqid(),
            'slug' => 'test-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ])->save();

        $blockedEvent = new BlockedEvent();
        $blockedEvent->forceFill([
            'from' => now(),
            'due' => now()->addHours(4),
            'type' => 'booking',
            'user_id' => $guide->id,
        ])->save();

        $booking = new Booking();
        $booking->forceFill([
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'status' => 'accepted',
            'token' => 'test-token-'.uniqid(),
            'is_guest' => true,
            'email' => 'guest@example.com',
            'count_of_users' => 1,
            'price' => 100,
        ])->save();

        return $booking;
    }

    private function insertQueuedJobFor(Booking $booking): int
    {
        $job = new FakeQueuedBookingJob($booking);

        return DB::table('jobs')->insertGetId([
            'queue' => 'default',
            'payload' => json_encode(['data' => ['command' => serialize($job)]]),
            'attempts' => 0,
            'available_at' => now()->getTimestamp(),
            'created_at' => now()->getTimestamp(),
        ]);
    }

    private function insertUnrelatedJob(): int
    {
        return DB::table('jobs')->insertGetId([
            'queue' => 'default',
            'payload' => json_encode(['data' => ['command' => serialize(new \stdClass())]]),
            'attempts' => 0,
            'available_at' => now()->getTimestamp(),
            'created_at' => now()->getTimestamp(),
        ]);
    }

    public function test_it_deletes_only_jobs_referencing_the_given_booking(): void
    {
        $booking = $this->createBooking();
        $otherBooking = $this->createBooking();

        $targetJobId = $this->insertQueuedJobFor($booking);
        $otherBookingJobId = $this->insertQueuedJobFor($otherBooking);
        $unrelatedJobId = $this->insertUnrelatedJob();

        $deleted = (new QueuedBookingJobPurger())->purge($booking);

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('jobs', ['id' => $targetJobId]);
        $this->assertDatabaseHas('jobs', ['id' => $otherBookingJobId]);
        $this->assertDatabaseHas('jobs', ['id' => $unrelatedJobId]);
    }

    public function test_it_returns_zero_when_nothing_references_the_booking(): void
    {
        $booking = $this->createBooking();
        $unrelatedJobId = $this->insertUnrelatedJob();

        $deleted = (new QueuedBookingJobPurger())->purge($booking);

        $this->assertSame(0, $deleted);
        $this->assertDatabaseHas('jobs', ['id' => $unrelatedJobId]);
    }
}

class FakeQueuedBookingJob
{
    use SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
}
