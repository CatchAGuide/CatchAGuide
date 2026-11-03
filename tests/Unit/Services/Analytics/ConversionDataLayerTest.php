<?php

namespace Tests\Unit\Services\Analytics;

use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\VoucherOrder;
use App\Services\Analytics\ConversionDataLayer;
use PHPUnit\Framework\TestCase;

class ConversionDataLayerTest extends TestCase
{
    private ConversionDataLayer $conversions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conversions = new ConversionDataLayer();
    }

    public function test_tour_booking_carries_value_id_and_hashed_email(): void
    {
        $booking = (new Booking())->forceFill(['id' => 42, 'price' => '349.90', 'email' => ' Guest@Example.com ']);

        $this->assertSame([
            'conversion' => [
                'product_type' => 'tour',
                'transaction_id' => 'tour-42',
                'currency' => 'EUR',
                'value' => 349.9,
            ],
            'user_data' => ['sha256_email_address' => hash('sha256', 'guest@example.com')],
        ], $this->conversions->forTourBooking($booking));
    }

    public function test_each_type_gets_its_own_transaction_prefix_and_currency(): void
    {
        $camp = (new CampVacationBooking())->forceFill(['id' => 7, 'estimated_total' => 1200, 'currency' => 'nok', 'email' => 'a@b.de']);
        $trip = (new TripBooking())->forceFill(['id' => 7, 'estimated_total' => 2580, 'currency' => 'EUR', 'email' => 'a@b.de']);
        $voucher = (new VoucherOrder())->forceFill(['id' => 7, 'price_total' => 150, 'currency' => null, 'buyer_email' => 'a@b.de']);

        $this->assertSame(['camp', 'camp-7', 'NOK', 1200.0], array_values($this->conversions->forCampRequest($camp)['conversion']));
        $this->assertSame(['trip', 'trip-7', 'EUR', 2580.0], array_values($this->conversions->forTripRequest($trip)['conversion']));
        $this->assertSame(['voucher', 'voucher-7', 'EUR', 150.0], array_values($this->conversions->forVoucherOrder($voucher)['conversion']));
    }

    public function test_unknown_price_and_invalid_email_are_left_out(): void
    {
        $camp = (new CampVacationBooking())->forceFill(['id' => 9, 'estimated_total' => null, 'currency' => 'EUR', 'email' => 'not-an-email']);

        $payload = $this->conversions->forCampRequest($camp);

        $this->assertArrayNotHasKey('value', $payload['conversion']);
        $this->assertArrayNotHasKey('user_data', $payload);
    }

    public function test_gmail_dots_are_dropped_before_hashing(): void
    {
        $this->assertSame(hash('sha256', 'johnsmith@gmail.com'), $this->conversions->hashEmail('John.Smith@Gmail.com'));
        $this->assertSame(hash('sha256', 'john.smith@example.com'), $this->conversions->hashEmail('john.smith@example.com'));
        $this->assertNull($this->conversions->hashEmail(null));
    }
}
