<?php

namespace Tests\Feature\Console;

use App\Models\CategoryPage;
use App\Models\Method;
use App\Models\Target;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuditCategoryPageTypesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dry_run_reports_but_does_not_modify_bad_rows(): void
    {
        $target = Target::query()->create(['name' => 'Karpfen', 'name_en' => 'Carp']);
        $orphan = CategoryPage::query()->create([
            'type' => 'karpfen',
            'source_id' => $target->id,
            'name' => 'Orphaned row',
            'slug' => 'audit-orphan-'.uniqid(),
        ]);

        $this->artisan('category-pages:audit-types')
            ->expectsOutputToContain('1 CategoryPage row(s)')
            ->expectsOutputToContain('Dry run')
            ->assertExitCode(0);

        $this->assertSame('karpfen', $orphan->fresh()->type);
    }

    public function test_fix_repairs_rows_with_an_inferable_source(): void
    {
        // targets/methods are separate auto-increment id spaces, so create a couple of filler
        // targets first to make sure the real target's id can't collide with the method's id
        // below — a collision would make the row genuinely ambiguous, which is covered by
        // test_dry_run_leaves_an_ambiguous_source_id_for_manual_review().
        Target::query()->create(['name' => 'Filler 1', 'name_en' => 'Filler 1']);
        Target::query()->create(['name' => 'Filler 2', 'name_en' => 'Filler 2']);
        $target = Target::query()->create(['name' => 'Zander', 'name_en' => 'Pikeperch']);
        $method = Method::query()->create(['name' => 'Ansitzangeln', 'name_en' => 'Bait fishing']);
        $this->assertNotSame($target->id, $method->id);

        $targetOrphan = CategoryPage::query()->create([
            'type' => 'some-species-slug',
            'source_id' => $target->id,
            'name' => 'Orphaned target row',
            'slug' => 'audit-target-orphan-'.uniqid(),
        ]);
        $methodOrphan = CategoryPage::query()->create([
            'type' => 'some-method-slug',
            'source_id' => $method->id,
            'name' => 'Orphaned method row',
            'slug' => 'audit-method-orphan-'.uniqid(),
        ]);
        $unresolvable = CategoryPage::query()->create([
            'type' => 'totally-orphaned',
            'source_id' => 999999999,
            'name' => 'Unresolvable row',
            'slug' => 'audit-unresolvable-'.uniqid(),
        ]);

        $exitCode = Artisan::call('category-pages:audit-types', ['--fix' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Repaired 2 row(s).', $output, "Full command output:\n{$output}");
        $this->assertStringContainsString('Could not infer a type for row IDs: '.$unresolvable->id, $output);

        $this->assertSame('Targets', $targetOrphan->fresh()->type);
        $this->assertSame('Methods', $methodOrphan->fresh()->type);
        $this->assertSame('totally-orphaned', $unresolvable->fresh()->type);
    }

    public function test_dry_run_leaves_an_ambiguous_source_id_for_manual_review(): void
    {
        $target = Target::query()->create(['name' => 'Hecht', 'name_en' => 'Pike']);
        // Force a colliding id in the separate `methods` id space so this source_id exists in
        // both tables at once — genuinely ambiguous, must not be auto-fixed either way.
        $method = Method::query()->create(['name' => 'Spinnfischen', 'name_en' => 'Spin fishing']);
        \Illuminate\Support\Facades\DB::table('methods')->where('id', $method->id)->delete();
        \Illuminate\Support\Facades\DB::table('methods')->insert([
            'id' => $target->id,
            'name' => 'Spinnfischen',
            'name_en' => 'Spin fishing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ambiguous = CategoryPage::query()->create([
            'type' => 'ambiguous-type',
            'source_id' => $target->id,
            'name' => 'Ambiguous row',
            'slug' => 'audit-ambiguous-'.uniqid(),
        ]);

        $this->artisan('category-pages:audit-types', ['--fix' => true])
            ->expectsOutputToContain('Could not infer a type for row IDs: '.$ambiguous->id)
            ->assertExitCode(0);

        $this->assertSame('ambiguous-type', $ambiguous->fresh()->type);
    }

    public function test_reports_clean_when_no_bad_rows_exist(): void
    {
        CategoryPage::query()->whereRaw('LOWER(type) NOT IN (?, ?)', ['methods', 'targets'])->delete();

        $this->artisan('category-pages:audit-types')
            ->expectsOutputToContain('No CategoryPage rows with an unexpected type found.')
            ->assertExitCode(0);
    }
}
