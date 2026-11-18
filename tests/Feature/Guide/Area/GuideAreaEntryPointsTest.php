<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\GuideStatus;
use App\Models\Employee;
use App\Models\User;
use App\Support\GuideAreaNav;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * How guides get into the Guide Area: admin beta switch, redirect from the old page, menu links.
 */
class GuideAreaEntryPointsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');
    }

    private function guide(bool $beta): User
    {
        $guide = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'language' => 'en',
        ]);
        $guide->forceFill(['guide_area_beta' => $beta])->save();

        return $guide->fresh();
    }

    public function test_every_guide_uses_the_guide_area_unless_the_kill_switch_is_off(): void
    {
        $this->assertTrue($this->guide(true)->usesGuideArea());
        // The old per-guide beta flag is no longer checked.
        $this->assertTrue($this->guide(false)->usesGuideArea());

        $customer = User::factory()->create(['is_guide' => 0, 'guide_status' => null]);
        $customer->forceFill(['guide_area_beta' => true])->save();
        $this->assertFalse($customer->fresh()->usesGuideArea());

        config(['guide_area.enabled' => false]);
        $this->assertFalse($this->guide(true)->usesGuideArea());
    }

    public function test_old_guide_profile_redirects_beta_guides_to_the_new_page(): void
    {
        $this->actingAs($this->guide(true))
            ->get(route('profile.guide-profile'))
            ->assertRedirect(route('guide.area.profile'));
    }

    public function test_old_guide_profile_still_serves_guides_when_the_kill_switch_is_off(): void
    {
        config(['guide_area.enabled' => false]);

        $this->actingAs($this->guide(false))
            ->get(route('profile.guide-profile'))
            ->assertOk();
    }

    public function test_profile_sends_guide_area_guides_to_the_overview_and_keeps_customers(): void
    {
        $this->actingAs($this->guide(true))
            ->get(route('profile.index'))
            ->assertRedirect(route('guide.area.overview'));

        $this->actingAs($this->guide(false))->get(route('profile.index'))->assertRedirect(route('guide.area.overview'));

        $customer = User::factory()->create(['is_guide' => 0, 'guide_status' => null]);
        $this->actingAs($customer)->get(route('profile.index'))->assertOk();
    }

    public function test_profile_menu_links_beta_guides_to_the_new_page(): void
    {
        $this->actingAs($this->guide(true))
            ->get(route('profile.settings'))
            ->assertOk()
            ->assertSee(route('guide.area.profile'), false);
    }

    public function test_admin_guide_edit_page_has_no_beta_switch_any_more(): void
    {
        $guide = $this->guide(false);
        $employee = Employee::query()->first() ?? Employee::factory()->create();

        $this->actingAs($employee, 'employees')
            ->get(route('admin.guides.edit', $guide))
            ->assertOk()
            ->assertDontSee('guide-area-beta');
    }

    public function test_every_sidebar_link_is_a_page_that_opens_with_get(): void
    {
        $this->actingAs($this->guide(true));

        foreach (GuideAreaNav::sections() as $section) {
            foreach ($section['items'] as $item) {
                $this->assertNotSame('#', $item['url'], $item['key'].' has no route');
                $route = Route::getRoutes()->match(request()->create($item['url'], 'GET'));
                $this->assertContains('GET', $route->methods(), $item['key'].' does not accept GET');
            }
        }
    }
}
