<?php

namespace Tests\Feature\Console;

use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FixDuplicateGuidingSlugsCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_keeps_oldest_and_rewrites_duplicates(): void
    {
        $shared = 'dup-command-slug-' . uniqid('', true);

        $keeper = $this->createGuiding([
            'title' => 'Whole Day Bellyboat',
            'location' => 'Bograngen, Zweden',
            'slug' => $shared,
        ]);

        $duplicate = $this->createGuiding([
            'title' => 'Eisangeln in Nord-Värmland',
            'location' => 'Bograngen, Zweden',
            'slug' => $shared,
        ]);

        $this->artisan('guidings:fix-duplicate-slugs')
            ->assertSuccessful();

        $keeper->refresh();
        $duplicate->refresh();

        $this->assertSame($shared, $keeper->slug);
        $this->assertNotSame($shared, $duplicate->slug);
        $this->assertStringContainsString('eisangeln', $duplicate->slug);
    }

    public function test_same_guide_cards_link_to_distinct_offer_urls_after_fix(): void
    {
        $shared = 'same-guide-card-slug-' . uniqid('', true);

        $primary = $this->createGuiding([
            'title' => 'Bellyboat Whole Day',
            'location' => 'Bograngen, Zweden',
            'slug' => $shared,
        ]);

        $other = $this->createGuiding([
            'title' => 'Eisangeln in Nord-Värmland',
            'location' => 'Bograngen, Zweden',
            'slug' => $shared,
        ]);

        $this->artisan('guidings:fix-duplicate-slugs')->assertSuccessful();

        $primary->refresh();
        $other->refresh();

        $this->assertNotSame($primary->publicShowUrl(), $other->publicShowUrl());
        $this->assertStringContainsString($other->slug, $other->publicShowUrl());
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
