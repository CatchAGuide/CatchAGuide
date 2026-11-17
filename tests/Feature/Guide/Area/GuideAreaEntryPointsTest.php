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

    public function test_uses_guide_area_needs_flag_kill_switch_and_guide_tools(): void
    {
        $this->assertTrue($this->guide(true)->usesGuideArea());
        $this->assertFalse($this->guide(false)->usesGuideArea());

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

    public function test_old_guide_profile_still_serves_guides_without_the_flag(): void
    {
        $this->actingAs($this->guide(false))
            ->get(route('profile.guide-profile'))
            ->assertOk();
    }

    public function test_profile_menu_links_beta_guides_to_the_new_page(): void
    {
        $this->actingAs($this->guide(true))
            ->get(route('profile.settings'))
            ->assertOk()
            ->assertSee(route('guide.area.profile'), false);
    }

    public function test_admin_can_toggle_the_beta_flag(): void
    {
        $guide = $this->guide(false);
        $employee = Employee::query()->first() ?? Employee::factory()->create();

        $this->actingAs($employee, 'employees')
            ->post(route('admin.guides.guide-area-beta', $guide))
            ->assertRedirect();
        $this->assertTrue($guide->fresh()->guide_area_beta);

        $this->actingAs($employee, 'employees')
            ->post(route('admin.guides.guide-area-beta', $guide))
            ->assertRedirect();
        $this->assertFalse($guide->fresh()->guide_area_beta);
    }

    public function test_guides_cannot_toggle_their_own_flag_through_the_admin_route(): void
    {
        $guide = $this->guide(false);

        $this->actingAs($guide)->post(route('admin.guides.guide-area-beta', $guide));

        $this->assertFalse($guide->fresh()->guide_area_beta);
    }

    public function test_admin_guide_edit_page_shows_the_switch(): void
    {
        $guide = $this->guide(false);
        $employee = Employee::query()->first() ?? Employee::factory()->create();

        $this->actingAs($employee, 'employees')
            ->get(route('admin.guides.edit', $guide))
            ->assertOk()
            ->assertSee(__('guide_area.admin.beta.enable'));
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
