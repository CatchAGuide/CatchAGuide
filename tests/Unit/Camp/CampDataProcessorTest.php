<?php

namespace Tests\Unit\Camp;

use App\Services\Camp\CampDataProcessor;
use Illuminate\Http\Request;
use Tests\TestCase;

class CampDataProcessorTest extends TestCase
{
    public function test_target_fish_csv_is_stored_as_an_array(): void
    {
        $data = $this->processor()->processRequestData(Request::create('/admin/camps', 'POST', [
            'title' => 'Camp',
            'target_fish' => 'Flussbarsch,Hecht,Äsche',
        ]));

        $this->assertSame(['Flussbarsch', 'Hecht', 'Äsche'], $data['target_fish']);
    }

    public function test_target_fish_tagify_json_prefers_ids(): void
    {
        $data = $this->processor()->processRequestData(Request::create('/admin/camps', 'POST', [
            'title' => 'Camp',
            'target_fish' => json_encode([
                ['id' => 12, 'value' => 'Flussbarsch'],
                ['value' => 'Custom carp'],
            ]),
        ]));

        $this->assertSame([12, 'Custom carp'], $data['target_fish']);
    }

    public function test_edit_form_stringifies_array_best_travel_times_for_textarea(): void
    {
        $camp = new \App\Models\Camp();
        $camp->forceFill([
            'id' => 1,
            'title' => 'Camp',
            'best_travel_times' => [
                ['month' => 'Mar', 'note' => 'Pike active'],
                ['month' => 'Oct', 'note' => 'Perch peak'],
            ],
            'gallery_images' => [],
            'target_fish' => [],
            'extras' => '',
        ]);
        $camp->syncOriginal();
        $camp->setRelation('facilities', collect());
        $camp->setRelation('accommodations', collect());
        $camp->setRelation('rentalBoats', collect());
        $camp->setRelation('guidings', collect());
        $camp->setRelation('specialOffers', collect());

        $form = $this->processor()->prepareEditFormData($camp);

        $this->assertIsString($form['best_travel_times']);
        $this->assertStringContainsString('Mar: Pike active', $form['best_travel_times']);
        $this->assertStringContainsString('Oct: Perch peak', $form['best_travel_times']);
    }

    private function processor(): CampDataProcessor
    {
        return new CampDataProcessor();
    }
}
