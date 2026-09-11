<?php

namespace Tests\Feature\Guidings;

use App\Http\Livewire\GuideThread;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * GuideThread's "Load More" re-ran the whole filtered query on every click AND
 * re-loaded every already-shown row's relations in a foreach N+1 loop (plus
 * computed-and-discarded target/method/water/inclusion lookup maps), so query
 * cost grew with how many rows were already on the page — the page got slower
 * the longer a visitor kept clicking "Load More" or touching a filter.
 *
 * This exercises the component directly (not via Livewire::test()'s HTTP round
 * trip, which 404s in this environment even for an unmodified bare mount — a
 * pre-existing gap, since no Livewire component test existed anywhere in this
 * repo before this file).
 */
class GuideThreadLoadMoreTest extends TestCase
{
    use DatabaseTransactions;

    private const GUIDING_COUNT = 12;

    private User $guide;

    private Target $target;

    private array $guidingIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->guide = User::factory()->create();

        // Target has no fillable/guarded override, so mass assignment is blocked; insert directly.
        $targetId = DB::table('targets')->insertGetId([
            'name' => 'Regression Test Pike',
            'name_en' => 'Regression Test Pike',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->target = Target::findOrFail($targetId);

        for ($i = 1; $i <= self::GUIDING_COUNT; $i++) {
            $guiding = Guiding::create([
                'title' => "Regression Test Guiding {$i}",
                'location' => "Bay {$i}",
                'max_guests' => 4,
                'duration' => 2,
                'price' => 50,
                'status' => 1,
                'fishing_type_id' => 1,
                'target_fish' => json_encode([$this->target->id]),
                'user_id' => $this->guide->id,
            ]);
            $this->guidingIds[] = $guiding->id;
        }
    }

    /**
     * sortBy=newest pulls our freshly-created rows to the front deterministically,
     * so assertions don't depend on the real (shared dev DB) total row count.
     */
    private function newComponent(): GuideThread
    {
        $component = new GuideThread();
        $component->mount();
        $component->sortBy = 'newest';

        return $component;
    }

    public function test_initial_page_is_entirely_our_newest_rows(): void
    {
        $component = $this->newComponent();
        $guidings = $component->render()->getData()['guidings'];

        $this->assertSame(5, $guidings->count());
        foreach ($guidings as $guiding) {
            $this->assertContains($guiding->id, $this->guidingIds);
        }
        $this->assertTrue($guidings->hasMorePages());
    }

    public function test_load_more_appends_without_duplicating_and_without_growing_query_cost(): void
    {
        $component = $this->newComponent();
        $component->render();

        DB::enableQueryLog();
        $component->loadMore();
        $guidingsAt10 = $component->render()->getData()['guidings'];
        $queriesAt10 = count(DB::getQueryLog());

        DB::flushQueryLog();
        $component->loadMore();
        $guidingsAt15 = $component->render()->getData()['guidings'];
        $queriesAt15 = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(10, $guidingsAt10->count());
        $this->assertSame(15, $guidingsAt15->count());

        $idsAt10 = $guidingsAt10->pluck('id')->all();
        $idsAt15 = $guidingsAt15->pluck('id')->all();
        $this->assertCount(count($idsAt10), array_unique($idsAt10), 'Load More must not duplicate rows.');
        $this->assertCount(count($idsAt15), array_unique($idsAt15), 'Load More must not duplicate rows.');
        $this->assertCount(12, array_intersect($this->guidingIds, $idsAt15));

        // Regression guard: the old code re-ran the whole query and re-loaded every
        // already-shown row's relations in a foreach loop (N+1), plus computed and
        // discarded 4 lookup maps, so query count scaled with the growing window
        // (roughly +2 queries per already-shown row, every single click). A window
        // that grew by 5 more rows must not cost meaningfully more queries — a swing
        // of 1 is tolerated since unrelated real rows sharing the page may or may not
        // need a target/inclusion lookup query.
        $this->assertLessThanOrEqual(1, abs($queriesAt15 - $queriesAt10), 'Query count must stay flat, not scale with page size.');
        $this->assertLessThan(15, $queriesAt10, 'Query count should stay small, not scale with page size.');
    }

    public function test_target_fish_names_use_the_batched_lookup_map_not_find_per_item(): void
    {
        $component = $this->newComponent();
        $data = $component->render()->getData();

        $targetsMap = $data['targetsMap'];

        $this->assertTrue($targetsMap->has($this->target->id));
        $this->assertSame('Regression Test Pike', $targetsMap->get($this->target->id)->name);
    }

    public function test_changing_a_filter_resets_pagination_back_to_first_page(): void
    {
        $component = $this->newComponent();
        $component->render();
        $component->loadMore();

        $this->assertSame(10, $component->render()->getData()['guidings']->count());

        $component->sortBy = 'price-asc';
        $component->updatedSortBy();

        $this->assertSame(5, $component->render()->getData()['guidings']->count());
    }
}
