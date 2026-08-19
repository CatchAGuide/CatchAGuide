<?php

namespace Tests\Unit\Vacation;

use App\Models\Camp;
use App\Models\CategoryPage;
use App\Models\Target;
use App\Models\Trip;
use App\Models\User;
use App\Services\Vacation\VacationTargetFishSelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VacationTargetFishSelectorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_forhub_counts_active_camps_and_trips_and_links_to_vacations_targets(): void
    {
        Cache::flush();

        $marker = 'test-fish-'.uniqid();
        $user = User::factory()->create();

        $target = new Target();
        $target->name = $marker;
        $target->save();

        CategoryPage::query()->create([
            'name' => $marker,
            'type' => 'Targets',
            'slug' => $marker,
            'source_id' => (string) $target->id,
            'is_favorite' => true,
        ]);

        Camp::query()->create([
            'title' => 'Camp '.$marker,
            'description_camp' => 'desc',
            'description_area' => 'desc',
            'description_fishing' => 'desc',
            'location' => 'Somewhere',
            'target_fish' => [$marker],
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        Trip::query()->create([
            'title' => 'Trip '.$marker,
            'slug' => $marker,
            'location' => 'Somewhere',
            'target_species' => [$target->id],
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $result = app(VacationTargetFishSelector::class)->forHub(200);
        $tile = $result->first(fn (array $row) => $row['slug'] === $marker);

        $this->assertNotNull($tile);
        $this->assertSame(2, $tile['count']);
        $this->assertSame(route('vacations.targets', ['slug' => $marker]), $tile['url']);
    }

    public function test_forhub_drops_species_with_no_active_listings(): void
    {
        Cache::flush();

        $marker = 'test-fish-empty-'.uniqid();
        $target = new Target();
        $target->name = $marker;
        $target->save();

        CategoryPage::query()->create([
            'name' => $marker,
            'type' => 'Targets',
            'slug' => $marker,
            'source_id' => (string) $target->id,
            'is_favorite' => true,
        ]);

        $result = app(VacationTargetFishSelector::class)->forHub(200);

        $this->assertNull($result->first(fn (array $row) => $row['slug'] === $marker));
    }
}
