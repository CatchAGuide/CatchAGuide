<?php

namespace Tests\Unit\Services\Guiding;

use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Guiding\GuidingSeoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GuidingSeoServiceTest extends TestCase
{
    use DatabaseTransactions;

    private GuidingSeoService $seo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seo = new GuidingSeoService();
    }

    public function test_generate_slug_uses_title_and_location(): void
    {
        $slug = $this->seo->generateSlug(
            'Eisangeln in Nord-Värmland',
            'Bograngen, Zweden'
        );

        $this->assertSame(
            'eisangeln-in-nord-varmland-in-bograngen-zweden',
            $slug
        );
    }

    public function test_generate_slug_appends_counter_when_taken(): void
    {
        $title = 'Collision Title ' . uniqid('', true);
        $location = 'Collision Loc';
        $expectedBase = $this->seo->baseSlug($title, $location);

        $this->createGuiding([
            'title' => $title,
            'location' => $location,
            'slug' => $expectedBase,
        ]);

        $unique = $this->seo->generateSlug($title, $location);

        $this->assertSame($expectedBase . '-1', $unique);
    }

    public function test_ensure_unique_slug_keeps_existing_when_not_colliding(): void
    {
        $slug = 'keep-this-slug-' . uniqid('', true);

        $guiding = $this->createGuiding([
            'title' => 'Changed Title',
            'location' => 'Somewhere',
            'slug' => $slug,
        ]);

        $this->assertSame($slug, $this->seo->ensureUniqueSlug($guiding, 'Changed Title', 'Somewhere'));
    }

    public function test_ensure_unique_slug_regenerates_on_collision(): void
    {
        $shared = 'shared-collision-slug-' . uniqid('', true);

        $this->createGuiding([
            'title' => 'Keeper',
            'location' => 'Loc',
            'slug' => $shared,
        ]);

        $duplicate = $this->createGuiding([
            'title' => 'Eisangeln in Nord-Värmland',
            'location' => 'Bograngen, Zweden',
            'slug' => $shared,
        ]);

        $newSlug = $this->seo->ensureUniqueSlug($duplicate);

        $this->assertNotSame($shared, $newSlug);
        $this->assertStringContainsString('eisangeln', $newSlug);
        $this->assertFalse($this->seo->slugExists($newSlug, $duplicate->id));
    }

    private function createGuiding(array $overrides): Guiding
    {
        $user = User::factory()->create();

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Test Tour',
            'slug' => 'test-tour-' . uniqid('', true),
            'location' => 'Test Loc',
            'status' => 1,
            'max_guests' => 2,
            'duration' => 3,
            'price' => 10,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
    }
}
