<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use PHPUnit\Framework\TestCase;

class BookingExtrasListTest extends TestCase
{
    private function bookingWithExtras(mixed $extras): Booking
    {
        $booking = new Booking();
        $booking->setRawAttributes(['extras' => $extras]);

        return $booking;
    }

    public function test_it_decodes_valid_serialized_extras(): void
    {
        $extras = [['extra_name' => 'Köderpaket', 'extra_total_price' => 100.0]];

        $this->assertSame($extras, $this->bookingWithExtras(serialize($extras))->extrasList());
    }

    public function test_it_repairs_double_utf8_encoded_extras(): void
    {
        // Booking #118 on prod: "Köerpaket" re-encoded as "KÃ¶erpaket" while the length prefix stayed 10.
        $broken = 'a:1:{i:0;a:5:{s:8:"extra_id";i:134;s:10:"extra_name";s:10:"KÃ¶erpaket";s:11:"extra_price";'
            . 's:5:"50.00";s:14:"extra_quantity";i:2;s:17:"extra_total_price";d:100;}}';

        $extras = $this->bookingWithExtras($broken)->extrasList();

        $this->assertCount(1, $extras);
        $this->assertSame('Köerpaket', $extras[0]['extra_name']);
        $this->assertSame(100.0, $extras[0]['extra_total_price']);
    }

    public function test_it_returns_empty_list_for_empty_or_garbage_extras(): void
    {
        $this->assertSame([], $this->bookingWithExtras(null)->extrasList());
        $this->assertSame([], $this->bookingWithExtras('')->extrasList());
        $this->assertSame([], $this->bookingWithExtras('not serialized')->extrasList());
    }

    public function test_it_does_not_instantiate_objects(): void
    {
        $extras = $this->bookingWithExtras(serialize([new \ArrayObject()]))->extrasList();

        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $extras[0]);
    }
}
