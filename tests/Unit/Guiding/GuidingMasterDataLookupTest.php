<?php

namespace Tests\Unit\Guiding;

use App\Models\Guiding;
use App\Models\Inclussion;
use App\Models\Target;
use App\Models\Water;
use App\Services\Guiding\GuidingMasterDataLookup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class GuidingMasterDataLookupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_name_getters_load_each_master_table_once_for_many_guidings(): void
    {
        $targets = [
            Target::query()->create(['name' => 'Testhecht', 'name_en' => 'Test pike']),
            Target::query()->create(['name' => 'Testzander', 'name_en' => 'Test zander']),
        ];
        $inclusion = Inclussion::query()->create(['name' => 'Testköder', 'name_en' => 'Test bait']);
        $guidings = collect(range(1, 5))->map(fn () => new Guiding([
            'target_fish' => json_encode([$targets[0]->id, $targets[1]->id, 'Freitext']),
            'inclusions' => json_encode([$inclusion->id]),
        ]));

        DB::enableQueryLog();
        DB::flushQueryLog();
        $names = $guidings->map(fn (Guiding $guiding) => [$guiding->getTargetFishNames(), $guiding->getInclusionNames()]);
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $this->assertSame(1, $queries->filter(fn ($sql) => str_contains($sql, 'from `targets`'))->count());
        $this->assertSame(1, $queries->filter(fn ($sql) => str_contains($sql, 'from `inclussions`'))->count());
        $this->assertSame([
            ['id' => $targets[0]->id, 'name' => $targets[0]->name],
            ['id' => $targets[1]->id, 'name' => $targets[1]->name],
            ['id' => null, 'name' => 'Freitext'],
        ], $names[4][0]);
        $this->assertSame([['id' => $inclusion->id, 'name' => $inclusion->name]], $names[4][1]);
    }

    public function test_an_unknown_id_is_kept_as_its_raw_value_like_before(): void
    {
        $guiding = new Guiding(['target_fish' => json_encode([999999999])]);

        $this->assertSame([['id' => null, 'name' => 999999999]], $guiding->getTargetFishNames());
    }

    public function test_a_new_request_scope_sees_new_rows(): void
    {
        $this->app->make(GuidingMasterDataLookup::class)->find(Water::class, 1);
        $water = Water::query()->create(['name' => 'Testsee', 'name_en' => 'Test lake']);

        $this->app->forgetScopedInstances();

        $this->assertTrue($water->is($this->app->make(GuidingMasterDataLookup::class)->find(Water::class, $water->id)));
    }

    public function test_only_small_master_tables_are_allowed(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->app->make(GuidingMasterDataLookup::class)->find(Guiding::class, 1);
    }
}
