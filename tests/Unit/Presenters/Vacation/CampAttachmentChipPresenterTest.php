<?php

namespace Tests\Unit\Presenters\Vacation;

use App\Presenters\Vacation\CampAttachmentChipPresenter;
use Tests\TestCase;

class CampAttachmentChipPresenterTest extends TestCase
{
    public function test_persons_value_formats_numeric_counts_with_the_shared_short_label(): void
    {
        $this->assertSame('4 '.__('vacations.pers_short'), CampAttachmentChipPresenter::personsValue(4));
        $this->assertSame('1 '.__('vacations.pers_short'), CampAttachmentChipPresenter::personsValue('1'));
    }

    public function test_persons_value_keeps_non_numeric_strings_and_skips_empty(): void
    {
        $this->assertSame('4 seats', CampAttachmentChipPresenter::personsValue('4 seats'));
        $this->assertNull(CampAttachmentChipPresenter::personsValue(null));
        $this->assertNull(CampAttachmentChipPresenter::personsValue(''));
        $this->assertNull(CampAttachmentChipPresenter::personsValue(0));
    }

    public function test_area_value_appends_the_sqm_unit_for_numeric_sizes(): void
    {
        $this->assertSame('80 '.__('vacations.unit_sqm'), CampAttachmentChipPresenter::areaValue(80));
        $this->assertSame('open plan', CampAttachmentChipPresenter::areaValue('open plan'));
        $this->assertNull(CampAttachmentChipPresenter::areaValue(null));
    }

    public function test_boat_chips_map_capacity_onto_the_shared_persons_chip(): void
    {
        $chips = CampAttachmentChipPresenter::boatChips([
            ['key' => 'capacity', 'label' => 'Capacity', 'value' => 3],
            ['key' => 'engine', 'label' => 'Engine', 'value' => '150 HP'],
            ['key' => 'license', 'label' => 'License', 'value' => 'Sportboot'],
            ['label' => 'Other', 'value' => 'GPS'],
        ]);

        $this->assertSame('persons', $chips[0]['type']);
        $this->assertSame('3 '.__('vacations.pers_short'), $chips[0]['value']);
        $this->assertNull($chips[0]['label']);

        $this->assertSame('engine', $chips[1]['type']);
        $this->assertSame('150 HP', $chips[1]['value']);
        $this->assertNull($chips[1]['label']);

        $this->assertSame('license', $chips[2]['type']);
        $this->assertSame('generic', $chips[3]['type']);
        $this->assertSame('Other', $chips[3]['label']);
    }

    public function test_tooltip_for_names_each_chip_type(): void
    {
        $this->assertSame(__('vacations.max_persons'), CampAttachmentChipPresenter::tooltipFor('persons'));
        $this->assertSame(__('guidings.Duration'), CampAttachmentChipPresenter::tooltipFor('duration'));
        $this->assertSame(__('vacations.chip_living_area'), CampAttachmentChipPresenter::tooltipFor('area'));
        $this->assertSame(__('rental_boats.engine'), CampAttachmentChipPresenter::tooltipFor('engine'));
        $this->assertSame('Custom', CampAttachmentChipPresenter::tooltipFor('generic', 'Custom'));
        $this->assertSame(__('vacations.chip_bedrooms'), CampAttachmentChipPresenter::tooltipFor('bedrooms'));
        $this->assertSame(__('vacations.chip_beds'), CampAttachmentChipPresenter::tooltipFor('bed'));
    }

    public function test_bedrooms_value_formats_counts_and_skips_empty(): void
    {
        $this->assertSame('3', CampAttachmentChipPresenter::bedroomsValue(3));
        $this->assertSame('2', CampAttachmentChipPresenter::bedroomsValue('2'));
        $this->assertNull(CampAttachmentChipPresenter::bedroomsValue(null));
        $this->assertNull(CampAttachmentChipPresenter::bedroomsValue(0));
        $this->assertNull(CampAttachmentChipPresenter::bedroomsValue('keine angabe'));
    }

    public function test_bed_chips_split_each_bed_type_into_its_own_badge(): void
    {
        app()->setLocale('de');
        $chips = CampAttachmentChipPresenter::bedChips([
            ['count' => 5, 'name' => 'Einzelbett', 'name_en' => 'Single Bed'],
            ['count' => 1, 'name' => 'Sofabett', 'name_en' => 'Sofa Bed'],
            ['count' => '', 'name' => 'Kinderbett'],
        ]);

        $this->assertCount(2, $chips);
        $this->assertSame('bed', $chips[0]['type']);
        $this->assertSame('(5) Einzelbett', $chips[0]['value']);
        $this->assertSame('(1) Sofabett', $chips[1]['value']);

        app()->setLocale('en');
        $enChips = CampAttachmentChipPresenter::bedChips([
            ['count' => 5, 'name' => 'Einzelbett', 'name_en' => 'Single Bed'],
        ]);
        $this->assertSame('(5) Single Bed', $enChips[0]['value']);
    }

    public function test_bed_chips_fall_back_to_splitting_a_summary_string(): void
    {
        $chips = CampAttachmentChipPresenter::bedChips([], '(5) Einzelbett, (1) Klappbett');

        $this->assertCount(2, $chips);
        $this->assertSame('(5) Einzelbett', $chips[0]['value']);
        $this->assertSame('(1) Klappbett', $chips[1]['value']);
    }
}
