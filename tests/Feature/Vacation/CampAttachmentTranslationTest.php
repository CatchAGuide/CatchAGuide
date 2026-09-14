<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
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
