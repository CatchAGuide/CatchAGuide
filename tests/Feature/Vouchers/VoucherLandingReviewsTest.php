<?php

namespace Tests\Feature\Vouchers;

use App\Models\Booking;
use App\Models\Guiding;
use App\Models\Review;
use App\Models\User;
use App\Models\UserGuest;
use App\Services\Voucher\VoucherLandingCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VoucherLandingReviewsTest extends TestCase
{
    use DatabaseTransactions;

    private const COMMENT = 'Ein großartiger Tag auf dem Wasser, der Guide hat uns alles erklärt und wir haben viel gefangen.';

    protected function setUp(): void
    {
        parent::setUp();

        if (! Guiding::query()->exists()) {
            $this->markTestSkipped('No guidings in the database.');
        }

        Cache::flush();
    }

    public function test_guest_reviews_show_the_guest_name_not_a_user_sharing_the_id(): void
    {
        // A registered account that shares the guest's id must not lend the review its name.
        $sharedId = User::query()->whereNotIn('id', UserGuest::query()->select('id'))->value('id');
        if ($sharedId === null) {
            $this->markTestSkipped('No user id free in user_guests.');
        }
        User::query()->whereKey($sharedId)->update(['firstname' => 'Test']);

        $guest = $this->guest(['firstname' => 'Ingrid', 'lastname' => 'Guest', 'email' => 'ingrid@example.org'], $sharedId);

        $this->review($this->booking($guest->id, true), $guest->id, minutesAgo: 1);

        $names = array_column(app(VoucherLandingCatalog::class)->reviews(), 'name');

        $this->assertContains('Ingrid', $names);
        $this->assertNotContains('Test', $names);
    }

    public function test_test_staff_and_self_reviews_are_left_out(): void
    {
        $guiding = Guiding::query()->firstOrFail();

        $testGuest = $this->guest(['firstname' => 'Test', 'lastname' => 'Kunde', 'email' => 'kunde@example.org']);
        $staffGuest = $this->guest(['firstname' => 'Anna', 'lastname' => 'Staff', 'email' => 'anna@catchaguide.de']);
        $realGuest = $this->guest(['firstname' => 'Karla', 'lastname' => 'Kundin', 'email' => 'karla@example.org']);

        $this->review($this->booking($testGuest->id, true, $guiding), $testGuest->id, minutesAgo: 1, guiding: $guiding);
        $this->review($this->booking($staffGuest->id, true, $guiding), $staffGuest->id, minutesAgo: 2, guiding: $guiding);
        // The tour's own guide reviewing it.
        $this->review($this->booking($guiding->user_id, false, $guiding), $guiding->user_id, minutesAgo: 3, guiding: $guiding);
        $this->review($this->booking($realGuest->id, true, $guiding), $realGuest->id, minutesAgo: 4, guiding: $guiding);

        $reviews = app(VoucherLandingCatalog::class)->reviews();
        $names = array_column($reviews, 'name');
        $guideName = trim((string) User::query()->whereKey($guiding->user_id)->value('firstname'));

        $this->assertSame('Karla', $names[0] ?? null);
        $this->assertNotContains('Test', $names);
        $this->assertNotContains('Anna', $names);
        $this->assertLessThanOrEqual(3, count($reviews));
        if ($guideName !== '') {
            $this->assertNotSame($guideName, $names[0] ?? null);
        }
    }

    private function guest(array $attributes, ?int $id = null): UserGuest
    {
        $guest = new UserGuest($attributes + ['address' => 'Hauptstr. 1', 'postal' => '10115', 'city' => 'Berlin', 'phone' => '123456']);
        if ($id !== null) {
            $guest->id = $id;
        }
        $guest->save();

        return $guest;
    }

    private function booking(int $userId, bool $isGuest, ?Guiding $guiding = null): Booking
    {
        $booking = new Booking;
        $booking->forceFill([
            'user_id' => $userId,
            'is_guest' => $isGuest,
            'guiding_id' => ($guiding ?? Guiding::query()->firstOrFail())->id,
        ])->saveQuietly();

        return $booking;
    }

    private function review(Booking $booking, int $userId, int $minutesAgo, ?Guiding $guiding = null): Review
    {
        $guiding ??= Guiding::query()->firstOrFail();

        $review = new Review([
            'comment' => self::COMMENT,
            'overall_score' => 10,
            'guide_score' => 10,
            'region_water_score' => 10,
            'user_id' => $userId,
            'guide_id' => $guiding->user_id,
            'booking_id' => $booking->id,
            'guiding_id' => $guiding->id,
            'is_automatic' => false,
        ]);
        // Newer than any real review, so these are the candidates the page picks from.
        $review->created_at = now()->addDay()->subMinutes($minutesAgo);
        $review->save();

        return $review;
    }
}
