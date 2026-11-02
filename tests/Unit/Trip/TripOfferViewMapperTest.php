<?php

namespace Tests\Unit\Trip;

use App\Models\Trip;
use App\Services\Trip\TripOfferViewMapper;
use Tests\TestCase;

class TripOfferViewMapperTest extends TestCase
{
    public function test_english_schedule_uses_day_for_the_german_day_marker(): void
    {
        app()->setLocale('en');

        $view = $this->mapper()->map($this->tripWithSchedule());

        $this->assertSame('Day 1', $view['trip_schedule'][0]['time']);
        $this->assertSame('Day 2-6', $view['trip_schedule'][1]['time']);
        $this->assertSame('Day 7', $view['trip_schedule'][2]['time']);
        $this->assertSame('Arrival', $view['trip_schedule'][0]['day_label']);
        $this->assertSame('05:30', $view['trip_schedule'][3]['time']);
        $this->assertSame('Tag along the shore', $view['trip_schedule'][3]['description']);
    }

    public function test_german_schedule_keeps_the_tag_day_marker(): void
    {
        app()->setLocale('de');

        $view = $this->mapper()->map($this->tripWithSchedule());

        $this->assertSame('Tag 1', $view['trip_schedule'][0]['time']);
        $this->assertSame('Tag 2-6', $view['trip_schedule'][1]['time']);
        $this->assertSame('Tag 7', $view['trip_schedule'][2]['time']);
    }

    public function test_english_schedule_localizes_a_day_marker_stored_on_the_label(): void
    {
        app()->setLocale('en');

        $trip = new Trip([
            'trip_schedule' => [
                ['time' => null, 'day_label' => 'Tag 3', 'description' => ''],
            ],
        ]);

        $view = $this->mapper()->map($trip);

        $this->assertSame('Day 3', $view['trip_schedule'][0]['day_label']);
    }

    private function mapper(): TripOfferViewMapper
    {
        return new TripOfferViewMapper();
    }

    private function tripWithSchedule(): Trip
    {
        return new Trip([
            'trip_schedule' => [
                ['time' => 'Tag 1', 'day_label' => 'Arrival', 'description' => 'Transfer to the hotel'],
                ['time' => 'Tag 2-6', 'day_label' => 'Fishing with a guide', 'description' => 'Five days on the water'],
                ['time' => 'Tag 7', 'day_label' => 'Departure', 'description' => 'Return home'],
                ['time' => '05:30', 'day_label' => 'Breakfast', 'description' => 'Tag along the shore'],
            ],
        ]);
    }
}
