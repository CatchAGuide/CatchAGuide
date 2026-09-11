<?php

namespace Tests\Feature\Admin;

use App\Models\Camp;
use App\Models\Employee;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage for PUT /admin/camps/{camp}: deselecting every guiding
 * in the admin camp form's multi-select previously left the old pivot rows
 * in place. A <select multiple> with nothing selected submits no field at
 * all, so the controller's `if ($request->has('guidings'))` guard skipped
 * the sync entirely — the update responded success:true but the removed
 * guiding stayed attached to the camp.
 */
class AdminCampGuidingsSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    private function makeCamp(): Camp
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        return Camp::query()->create([
            'title' => 'Test Camp',
            'slug' => 'test-camp-' . uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $user->id,
        ]);
    }

    /**
     * @return array{0: Guiding, 1: Guiding}
     */
    private function makeGuidings(): array
    {
        $template = Guiding::query()->first();
        if (! $template) {
            $this->markTestSkipped('No existing guiding available to template test guidings from.');
        }

        $user = User::query()->first();

        $makeOne = function (string $title) use ($template, $user) {
            $guiding = $template->replicate();
            $guiding->title = $title;
            $guiding->slug = null;
            if ($user) {
                $guiding->user_id = $user->id;
            }
            $guiding->save();

            return $guiding;
        };

        return [$makeOne('Test Guiding One'), $makeOne('Test Guiding Two')];
    }

    public function test_removing_all_guidings_from_a_camp_persists_the_removal(): void
    {
        $this->actingAsEmployee();
        $camp = $this->makeCamp();
        [$guidingOne, $guidingTwo] = $this->makeGuidings();

        $camp->guidings()->sync([$guidingOne->id, $guidingTwo->id]);
        $this->assertCount(2, $camp->guidings()->get());

        // Simulate the multi-select being cleared: the browser sends no
        // "guidings" field at all in that case.
        $response = $this->putJson(route('admin.camps.update', $camp), [
            'title' => $camp->title,
            'location' => $camp->location,
            'is_draft' => '0',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertCount(
            0,
            $camp->guidings()->get(),
            'Deselecting every guiding in the form should clear the camp_guiding pivot, not leave it untouched.'
        );
    }

    public function test_updating_guidings_selection_syncs_the_new_set(): void
    {
        $this->actingAsEmployee();
        $camp = $this->makeCamp();
        [$guidingOne, $guidingTwo] = $this->makeGuidings();

        $camp->guidings()->sync([$guidingOne->id]);

        $response = $this->putJson(route('admin.camps.update', $camp), [
            'title' => $camp->title,
            'location' => $camp->location,
            'is_draft' => '0',
            'guidings' => [$guidingTwo->id],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $ids = $camp->guidings()->get()->pluck('id')->all();
        $this->assertSame([$guidingTwo->id], $ids);
    }
}
