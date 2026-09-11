<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage for the camp product page (CampOfferController::show,
 * pages.vacations.v2): a camp's attached guiding showed "€0.00" instead of a
 * real price whenever the guiding used per-person pricing tiers. The flat
 * `price` column - which mapGuidingData()/mapGuidingForDropdown() read
 * directly - is only populated for fixed/per-boat guidings; per-person
 * guidings store their tiers in `prices` and leave `price` empty, so the raw
 * column always rendered as 0.
 */
class CampGuidingDisplayPriceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function makeCamp(): Camp
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        return Camp::query()->create([
            'title' => 'Test Camp With Guiding',
            'slug' => 'test-camp-with-guiding-' . uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $user->id,
        ]);
    }

    private function makePerPersonGuiding(): Guiding
    {
        $template = Guiding::query()->first();
        if (! $template) {
            $this->markTestSkipped('No existing guiding available to template a test guiding from.');
        }

        $guiding = $template->replicate();
        $guiding->title = 'Per-Person Priced Guiding';
        $guiding->slug = null;
        $guiding->price_type = 'per_person';
        $guiding->price = null;
        $guiding->prices = json_encode([
            ['person' => '1', 'amount' => '600'],
            ['person' => '2', 'amount' => '600'],
            ['person' => '3', 'amount' => '750'],
        ]);
        $guiding->save();

        return $guiding;
    }

    public function test_camp_page_shows_the_per_person_guidings_lowest_price_instead_of_zero(): void
    {
        $camp = $this->makeCamp();
        $guiding = $this->makePerPersonGuiding();
        $camp->guidings()->sync([$guiding->id]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        // 600/1=600, 600/2=300, 750/3=250 -> lowest per-person price is 250.
        $response->assertSee('250.00', false);
        $response->assertDontSee('€0.00', false);
    }
}
