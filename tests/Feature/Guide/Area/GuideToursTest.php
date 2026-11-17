<?php

namespace Tests\Feature\Guide\Area;

use App\Contracts\Storage\ObjectStorageInterface;
use App\Enums\GuideStatus;
use App\Enums\TourStatus;
use App\Http\Livewire\Guide\Tours;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Media\MediaEnvironmentResolver;
use App\Services\Media\MediaPathResolver;
use App\Services\Media\MediaWriteStorageResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guide Area › My fishing tours: page (/guide/tours) and API (/api/guide/tours).
 */
class GuideToursTest extends TestCase
{
    use DatabaseTransactions;

    private ObjectStorageInterface $storage;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');

        $this->storage = new class implements ObjectStorageInterface
        {
            public array $files = [];

            public function exists(string $path): bool { return isset($this->files[$path]); }
            public function read(string $path): string { return $this->files[$path] ?? ''; }
            public function url(string $path): string { return 'https://cdn.test/'.$path; }
            public function write(string $path, string $contents, array $options = []): bool { $this->files[$path] = $contents; return true; }
            public function delete(string $path): bool { unset($this->files[$path]); return true; }
            public function deleteMany(array $paths): void { foreach ($paths as $p) { unset($this->files[$p]); } }
            public function makeDirectory(string $path): bool { return true; }
        };
        $this->app->instance(MediaWriteStorageResolver::class, new MediaWriteStorageResolver($this->storage, $this->storage));
        $this->app->instance(MediaPathResolver::class, new MediaPathResolver($this->storage, $this->storage, app(MediaEnvironmentResolver::class)));
    }

    private function guide(bool $beta = true, string $status = GuideStatus::VERIFIED): User
    {
        $guide = User::factory()->create(['is_guide' => 1, 'guide_status' => $status, 'language' => 'en']);
        $guide->forceFill(['guide_area_beta' => $beta])->save();

        return $guide->fresh();
    }

    private function tour(User $guide, int $status, bool $complete = true, array $extra = []): Guiding
    {
        $gallery = ['guidings/seed/a.webp', 'guidings/seed/b.webp', 'guidings/seed/c.webp', 'guidings/seed/d.webp', 'guidings/seed/e.webp'];
        foreach ($gallery as $path) {
            $this->storage->files[$path] = 'img:'.$path;
        }

        $guiding = new Guiding;
        $guiding->forceFill(array_merge([
            'title' => 'Zander on the Elbe '.uniqid(),
            'slug' => 'zander-elbe-'.uniqid(),
            'location' => 'Hamburg, Germany',
            'status' => $status,
            'user_id' => $guide->id,
            'max_guests' => 4,
            'duration' => 6,
            'duration_type' => 'hours',
            'price' => 300,
            'price_type' => 'per_tour',
            'tour_type' => 'private',
            'fishing_type_id' => FishingType::query()->value('id') ?? 1,
            'target_fish' => json_encode([1]),
            'fishing_methods' => json_encode([1]),
            'water_types' => json_encode([1]),
            'desc_course_of_action' => 'We start at the harbour.',
            'desc_meeting_point' => 'Harbour',
            'desc_tour_unique' => 'Big zander',
            'desc_starting_time' => '06:00',
            'allowed_booking_advance' => 'same_day',
            'booking_window' => 'twelve_months',
            'seasonal_trip' => 'year_round',
            'is_boat' => 0,
            'gallery_images' => json_encode($gallery),
            'thumbnail_path' => $gallery[0],
        ], $complete ? [] : ['desc_meeting_point' => null, 'gallery_images' => json_encode([])], $extra))->save();

        return $guiding->fresh();
    }

    public function test_tour_status_maps_the_stored_values(): void
    {
        $this->assertSame(TourStatus::Draft, TourStatus::tryFrom(2));
        $this->assertSame(TourStatus::Live, TourStatus::tryFrom(1));
        $this->assertSame(TourStatus::Deactivated, TourStatus::tryFrom(0));
        $this->assertSame(TourStatus::Live, TourStatus::fromKey('live'));
        $this->assertNull(TourStatus::fromKey('nope'));
    }

    public function test_publishing_gaps_match_is_complete_for_publishing(): void
    {
        $guide = $this->guide();
        $complete = $this->tour($guide, 2);
        $incomplete = $this->tour($guide, 2, complete: false);

        $this->assertSame([], $complete->publishingGaps());
        $this->assertTrue($complete->isCompleteForPublishing());
        $this->assertSame(['description', 'photos'], $incomplete->publishingGaps());
        $this->assertFalse($incomplete->isCompleteForPublishing());
    }

    public function test_page_lists_only_own_tours_with_tab_counts(): void
    {
        $guide = $this->guide();
        $live = $this->tour($guide, 1);
        $draft = $this->tour($guide, 2);
        $other = $this->tour($this->guide(), 1);

        $this->actingAs($guide)->get(route('guide.area.tours'))
            ->assertOk()
            ->assertSee($live->title)
            ->assertSee($draft->title)
            ->assertDontSee($other->title);

        Livewire::actingAs($guide)->test(Tours::class)
            ->call('setStatus', 'draft')
            ->assertSee($draft->title)
            ->assertDontSee($live->title)
            ->assertViewHas('counts', fn (array $c) => $c['all'] === 2 && $c['live'] === 1 && $c['draft'] === 1 && $c['deactivated'] === 0);
    }

    public function test_go_live_publishes_a_complete_draft(): void
    {
        $guide = $this->guide();
        $draft = $this->tour($guide, 2);

        Livewire::actingAs($guide)->test(Tours::class)->call('goLive', $draft->id);

        $this->assertSame(1, (int) $draft->fresh()->status);
    }

    public function test_go_live_refuses_incomplete_tours_and_lists_the_gaps(): void
    {
        $guide = $this->guide();
        $draft = $this->tour($guide, 2, complete: false);

        Livewire::actingAs($guide)->test(Tours::class)
            ->call('goLive', $draft->id)
            ->assertSet('gapsFor.'.$draft->id, [__('guide_area.tours.gaps.description'), __('guide_area.tours.gaps.photos')]);

        $this->assertSame(2, (int) $draft->fresh()->status);
    }

    public function test_pending_guides_cannot_go_live(): void
    {
        $guide = $this->guide(status: GuideStatus::PENDING);
        $draft = $this->tour($guide, 2);

        Sanctum::actingAs($guide);
        $this->postJson(route('api.guide.tours.go-live', $draft))
            ->assertStatus(422)
            ->assertJsonPath('reason', 'not_verified');

        $this->assertSame(2, (int) $draft->fresh()->status);
    }

    public function test_deactivate_takes_a_live_tour_offline_but_keeps_drafts(): void
    {
        $guide = $this->guide();
        $live = $this->tour($guide, 1);
        $draft = $this->tour($guide, 2);

        Livewire::actingAs($guide)->test(Tours::class)
            ->call('deactivate', $live->id)
            ->call('deactivate', $draft->id);

        $this->assertSame(0, (int) $live->fresh()->status);
        $this->assertSame(2, (int) $draft->fresh()->status);
    }

    public function test_duplicate_creates_a_draft_with_its_own_photo_copies(): void
    {
        $guide = $this->guide();
        $original = $this->tour($guide, 1);

        Livewire::actingAs($guide)->test(Tours::class)->call('duplicate', $original->id)->assertSet('status', 'draft');

        $copy = Guiding::query()->where('duplicated_from_id', $original->id)->firstOrFail();
        $this->assertSame(2, (int) $copy->status);
        $this->assertSame(__('guide_area.tours.copy_title', ['title' => $original->title]), $copy->title);
        $this->assertNotSame($original->slug, $copy->slug);
        $this->assertSame($original->desc_tour_unique, $copy->desc_tour_unique);
        $this->assertSame($original->target_fish, $copy->target_fish, 'JSON columns must not be double-encoded.');

        $gallery = json_decode($copy->gallery_images, true);
        $this->assertCount(5, $gallery);
        foreach ($gallery as $path) {
            $this->assertStringStartsWith('guidings/'.$copy->id.'/', $path);
            $this->assertArrayHasKey($path, $this->storage->files);
        }
        $this->assertContains($copy->thumbnail_path, $gallery);
        $this->assertArrayHasKey('guidings/seed/a.webp', $this->storage->files, 'The original photos stay.');
    }

    public function test_guides_cannot_act_on_other_guides_tours(): void
    {
        $guide = $this->guide();
        $foreign = $this->tour($this->guide(), 1);

        Sanctum::actingAs($guide);
        $this->postJson(route('api.guide.tours.deactivate', $foreign))->assertForbidden();
        $this->postJson(route('api.guide.tours.duplicate', $foreign))->assertForbidden();
        $this->assertSame(1, (int) $foreign->fresh()->status);

        // The page looks tours up through the guide's own ones: a foreign id is "not found".
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($guide)->test(Tours::class)->call('deactivate', $foreign->id);
    }

    public function test_api_lists_tours_with_counts(): void
    {
        $guide = $this->guide();
        $this->tour($guide, 1);
        $draft = $this->tour($guide, 2, complete: false);

        Sanctum::actingAs($guide);
        $this->getJson(route('api.guide.tours.index', ['status' => 'draft']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $draft->id)
            ->assertJsonPath('data.0.status', 'draft')
            ->assertJsonPath('data.0.can_go_live', false)
            ->assertJsonPath('data.0.gaps', ['description', 'photos'])
            ->assertJsonPath('meta.counts.all', 2);
    }

    public function test_api_duplicate_returns_the_new_draft(): void
    {
        $guide = $this->guide();
        $original = $this->tour($guide, 1);

        Sanctum::actingAs($guide);
        $this->postJson(route('api.guide.tours.duplicate', $original))
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.duplicated_from_id', $original->id);
    }

    public function test_api_requires_auth_and_the_beta_flag(): void
    {
        $this->getJson(route('api.guide.tours.index'))->assertUnauthorized();

        Sanctum::actingAs($this->guide(beta: false));
        $this->getJson(route('api.guide.tours.index'))->assertForbidden();
    }

    public function test_old_tours_page_redirects_beta_guides(): void
    {
        $this->actingAs($this->guide())->get(route('profile.myguidings'))->assertRedirect(route('guide.area.tours'));
    }
}
