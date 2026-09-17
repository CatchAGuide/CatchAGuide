<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\User;
use App\Models\Water;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CampAttachmentTranslationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_camp_page_shows_target_fish_from_the_targets_table(): void
    {
        $target = new Target();
        $target->name = 'Hecht-'.uniqid();
        $target->name_en = 'Pike-'.uniqid();
        $target->save();

        $camp = $this->makeCamp([
            'target_fish' => [$target->id],
        ]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee($target->name, false);
        $otherLocaleName = app()->getLocale() === 'en'
            ? $target->getAttributes()['name']
            : $target->getAttributes()['name_en'];
        if (filled($otherLocaleName) && $otherLocaleName !== $target->name) {
            $response->assertDontSee($otherLocaleName, false);
        }
        $response->assertDontSee('>'.$target->id.'<', false);
    }

    public function test_camp_page_decodes_json_encoded_target_fish_csv(): void
    {
        $camp = $this->makeCamp();
        DB::table('camps')->where('id', $camp->id)->update([
            'target_fish' => json_encode('Flussbarsch,Hecht,Äsche,Bachsaibling'),
        ]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee('camp-pill">Flussbarsch', false);
        $response->assertSee('camp-pill">Äsche', false);
        $response->assertSee('camp-pill">Hecht', false);
        $response->assertSee('camp-pill">Bachsaibling', false);
        $response->assertDontSee('camp-pill">"Flussbarsch', false);
        $response->assertDontSee('camp-pill">Bachsaibling"', false);
        $response->assertDontSee('&Auml;sche', false);
    }

    public function test_custom_camp_extras_use_the_cached_translate_helper(): void
    {
        $source = 'Custom camp extra '.uniqid();
        $translated = 'Cached camp extra '.uniqid();
        Cache::forever(translation_cache_key($source, app()->getLocale()), $translated);

        $camp = $this->makeCamp([
            'extras' => $source,
        ]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee($translated, false);
        $response->assertSee('camp-pill">'.$translated, false);
    }

    public function test_attached_guiding_target_fish_come_from_the_targets_table(): void
    {
        $target = new Target();
        $target->name = 'Zander-'.uniqid();
        $target->name_en = 'Zander-en-'.uniqid();
        $target->save();

        $camp = $this->makeCamp();
        $guiding = $this->makeGuiding([
            'target_fish' => json_encode([$target->id]),
        ]);
        $camp->guidings()->sync([$guiding->id]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee($target->name, false);
        $otherLocaleName = app()->getLocale() === 'en'
            ? $target->getAttributes()['name']
            : $target->getAttributes()['name_en'];
        if (filled($otherLocaleName) && $otherLocaleName !== $target->name) {
            $response->assertDontSee($otherLocaleName, false);
        }
    }

    public function test_attached_guiding_shows_catalog_water_types(): void
    {
        $water = Water::query()->first();
        if (! $water) {
            $this->markTestSkipped('No water type catalog row available.');
        }

        $camp = $this->makeCamp();
        $guiding = $this->makeGuiding([
            'water_types' => json_encode([$water->id]),
        ]);
        $camp->guidings()->sync([$guiding->id]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee('attachment-chip--water-type', false);
        $response->assertSee($water->name, false);
        $otherLocaleName = app()->getLocale() === 'en'
            ? $water->getAttributes()['name']
            : $water->getAttributes()['name_en'];
        if (filled($otherLocaleName) && $otherLocaleName !== $water->name) {
            $response->assertDontSee($otherLocaleName, false);
        }
        $response->assertDontSee('gewaesser">Water', false);
    }

    public function test_attached_guiding_shows_desc_meeting_point_not_the_legacy_column(): void
    {
        $meetingDescription = 'Harbour office gate 3 '.uniqid();
        $legacyMeetingPoint = 'Legacy short meeting '.uniqid();

        $camp = $this->makeCamp();
        $guiding = $this->makeGuiding([
            'desc_meeting_point' => $meetingDescription,
            'meeting_point' => $legacyMeetingPoint,
        ]);
        $camp->guidings()->sync([$guiding->id]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee($meetingDescription, false);
        $response->assertSee(__('guidings.Meeting_Point'), false);
        $response->assertDontSee($legacyMeetingPoint, false);
    }

    public function test_attached_guiding_shows_shore_or_boat_instead_of_private(): void
    {
        $camp = $this->makeCamp();
        $guiding = $this->makeGuiding([
            'tour_type' => 'private',
            'is_boat' => 1,
            'fishing_from_id' => null,
        ]);
        $camp->guidings()->sync([$guiding->id]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee(__('guidings.boat'), false);
        $response->assertDontSee('>private<', false);
        $response->assertDontSee('>Private<', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeCamp(array $overrides = []): Camp
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        return Camp::query()->create(array_merge([
            'title' => 'Translation Camp '.uniqid(),
            'slug' => 'translation-camp-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeGuiding(array $overrides = []): Guiding
    {
        $template = Guiding::query()->first();
        if (! $template) {
            $this->markTestSkipped('No existing guiding available to template a test guiding from.');
        }

        $guiding = $template->replicate();
        $guiding->title = 'Attached guiding '.uniqid();
        $guiding->slug = null;
        foreach ($overrides as $key => $value) {
            $guiding->{$key} = $value;
        }
        $guiding->save();

        return $guiding;
    }
}
