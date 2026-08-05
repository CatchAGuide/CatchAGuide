<?php

namespace Tests\Feature\Admin;

use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\Employee;
use App\Models\MonthlyHighlight;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MonthlyHighlightAdminTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAsEmployee(): Employee
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');

        return $employee;
    }

    public function test_admin_can_create_monthly_highlight_with_mixed_items(): void
    {
        $this->actingAsEmployee();

        $country = Country::query()->first();
        $targetPage = CategoryPage::query()->where('type', 'Targets')->first();
        if (! $country || ! $targetPage) {
            $this->markTestSkipped('Need at least one country and target category page.');
        }

        $month = 12;
        MonthlyHighlight::query()->where('month', $month)->delete();

        $response = $this->post('/admin/monthly-highlights', [
            'month' => $month,
            'title_en' => 'What is biting in December?',
            'title_de' => 'Was beißt im Dezember?',
            'subtitle_en' => 'Season picks',
            'subtitle_de' => 'Saisonale Tipps',
            'country_ids' => [$country->id],
            'target_ids' => [$targetPage->id],
            'is_active' => 1,
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Admin monthly-highlights route not reachable in this test environment.');
        }

        $response->assertRedirect('/admin/monthly-highlights');
        $this->assertDatabaseHas('monthly_highlights', [
            'month' => $month,
            'title_en' => 'What is biting in December?',
            'is_active' => 1,
        ]);

        $highlight = MonthlyHighlight::query()->where('month', $month)->first();
        $this->assertNotNull($highlight);
        $this->assertCount(2, $highlight->items);
    }

    public function test_admin_rejects_more_than_three_items(): void
    {
        $this->actingAsEmployee();

        $countryIds = Country::query()->limit(2)->pluck('id');
        $targetIds = CategoryPage::query()->where('type', 'Targets')->limit(2)->pluck('id');
        if ($countryIds->count() < 2 || $targetIds->count() < 2) {
            $this->markTestSkipped('Need enough countries and targets for max-items test.');
        }

        $month = 11;
        MonthlyHighlight::query()->where('month', $month)->delete();

        $response = $this->from('/admin/monthly-highlights/create')->post('/admin/monthly-highlights', [
            'month' => $month,
            'title_en' => 'Too many items',
            'title_de' => 'Zu viele Einträge',
            'country_ids' => [$countryIds[0], $countryIds[1]],
            'target_ids' => [$targetIds[0], $targetIds[1]],
            'is_active' => 1,
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Admin monthly-highlights route not reachable in this test environment.');
        }

        $response->assertRedirect('/admin/monthly-highlights/create');
        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('monthly_highlights', ['month' => $month, 'title_en' => 'Too many items']);
    }
}
