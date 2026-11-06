<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CampBestTravelTimesPdpTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_same_line_season_prose_renders_on_camp_pdp(): void
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        $camp = Camp::query()->create([
            'title' => 'Travel Times Test Camp',
            'slug' => 'travel-times-test-camp-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $user->id,
            'best_travel_times' => "Frühling (April – Mai): Mit dem Aufbrechen des Eises erwacht der See.\n\n"
                .'Sommer (Juni – August): Die Hochsaison mit langen Tagen.',
            'gallery_images' => [],
            'target_fish' => [],
        ]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee('best-travel-times', false);
        $response->assertSee('Frühling (April – Mai)', false);
        $response->assertSee('Mit dem Aufbrechen des Eises erwacht der See.', false);
        $response->assertSee('Sommer (Juni – August)', false);
        $response->assertSee('Die Hochsaison mit langen Tagen.', false);
    }
}
