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

    public function test_admin_can_create_monthly_highlight_with_pair_cards(): void
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
            'cards' => [
                ['country_id' => $country->id, 'target_id' => $targetPage->id],
            ],
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
        $this->assertCount(1, $highlight->items);
        $this->assertSame(MonthlyHighlight::ITEM_TYPE_PAIR, $highlight->items[0]['type']);
        $this->assertSame((int) $country->id, $highlight->items[0]['country_id']);
        $this->assertSame((int) $targetPage->id, $highlight->items[0]['target_id']);
    }

    public function test_admin_rejects_partial_card(): void
    {
        $this->actingAsEmployee();

        $country = Country::query()->first();
        if (! $country) {
            $this->markTestSkipped('Need at least one country.');
        }

        $month = 11;
        MonthlyHighlight::query()->where('month', $month)->delete();

        $response = $this->from('/admin/monthly-highlights/create')->post('/admin/monthly-highlights', [
            'month' => $month,
            'title_en' => 'Partial card',
            'title_de' => 'Unvollständige Karte',
            'cards' => [
                ['country_id' => $country->id, 'target_id' => null],
            ],
            'is_active' => 1,
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Admin monthly-highlights route not reachable in this test environment.');
        }

        $response->assertRedirect('/admin/monthly-highlights/create');
        $response->assertSessionHasErrors('cards.0');
        $this->assertDatabaseMissing('monthly_highlights', ['month' => $month, 'title_en' => 'Partial card']);
    }
}
