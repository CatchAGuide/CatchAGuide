<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * The camp page hands its data to Alpine inside a double-quoted x-data attribute. Raw JSON there
 * ends the attribute at the first quote and turns every following word into an attribute name, so
 * a description containing the word "hidden" hid the whole page (Flightmode Adventures, EN only).
 */
class CampConfiguratorAttributeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_camp_data_stays_inside_the_x_data_attribute(): void
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        $camp = Camp::query()->create([
            'title' => 'Test Camp Attribute',
            'slug' => 'test-camp-attribute-'.uniqid(),
            'description_camp' => 'One of the "hidden" classics & the river\'s best <b>spot</b>',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'city' => 'Test Waters',
            'region' => 'Värmland',
            'country' => 'Sweden',
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $html = $this->get(route('vacations.camps.show', $camp->slug))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<div\s+x-data="(campConfigurator\(\{.*?\}\))"\s+x-init="init\(\)"\s+class="camp-page/s', $html, $m),
            'x-data attribute must run unbroken up to x-init / class');
        $this->assertStringNotContainsString('"', $m[1]);
        $this->assertStringContainsString('camp: JSON.parse(', $m[1]);
    }
}
