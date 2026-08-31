<?php

namespace Tests\Unit\Admin;

use App\Http\Requests\Admin\StoreMonthlyHighlightRequest;
use App\Models\CategoryEntity;
use App\Models\CategoryPage;
use App\Models\MonthlyHighlight;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MonthlyHighlightValidationTest extends TestCase
{
    public function test_store_request_rejects_partial_card(): void
    {
        $country = CategoryEntity::countries()->first();
        if (! $country) {
            $this->markTestSkipped('Need at least one country.');
        }

        $request = StoreMonthlyHighlightRequest::create('/admin/monthly-highlights', 'POST', [
            'month' => 10,
            'title_en' => 'Partial card',
            'title_de' => 'Unvollständige Karte',
            'cards' => [
                ['country_id' => $country->id, 'target_id' => null],
            ],
            'is_active' => 1,
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));

        try {
            $request->validateResolved();
            $this->fail('Expected validation to fail for partial country/target card.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cards.0', $e->errors());
        }
    }

    public function test_store_request_builds_pair_items_from_cards(): void
    {
        $country = CategoryEntity::countries()->first();
        $targetPage = CategoryPage::query()->where('type', 'Targets')->first();
        if (! $country || ! $targetPage) {
            $this->markTestSkipped('Need at least one country and target category page.');
        }

        $request = StoreMonthlyHighlightRequest::create('/admin/monthly-highlights', 'POST', [
            'month' => 3,
            'title_en' => 'March picks',
            'title_de' => 'März Tipps',
            'cards' => [
                ['country_id' => $country->id, 'target_id' => $targetPage->id],
                ['country_id' => null, 'target_id' => null],
                ['country_id' => null, 'target_id' => null],
            ],
            'is_active' => 1,
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        $validated = $request->validated();
        $this->assertCount(1, $validated['items']);
        $this->assertSame(MonthlyHighlight::ITEM_TYPE_PAIR, $validated['items'][0]['type']);
        $this->assertSame((int) $country->id, $validated['items'][0]['country_id']);
        $this->assertSame((int) $targetPage->id, $validated['items'][0]['target_id']);
    }

    public function test_model_normalizes_pairs_and_caps_items(): void
    {
        $highlight = new MonthlyHighlight([
            'items' => [
                ['type' => 'pair', 'country_id' => 1, 'target_id' => 2],
                ['type' => 'pair', 'country_id' => 3, 'target_id' => 4],
                ['type' => 'pair', 'country_id' => 5, 'target_id' => 6],
                ['type' => 'pair', 'country_id' => 7, 'target_id' => 8],
                ['type' => 'invalid', 'id' => 5],
            ],
        ]);

        $items = $highlight->normalizedItems();
        $this->assertCount(3, $items);
        $this->assertSame('pair', $items[0]['type']);
        $this->assertSame(1, $items[0]['country_id']);
        $this->assertSame(2, $items[0]['target_id']);
    }

    public function test_model_still_normalizes_legacy_items(): void
    {
        $highlight = new MonthlyHighlight([
            'items' => [
                ['type' => 'country', 'id' => 1],
                ['type' => 'target', 'id' => 2],
            ],
        ]);

        $items = $highlight->normalizedItems();
        $this->assertCount(2, $items);
        $this->assertSame('country', $items[0]['type']);
        $this->assertSame(1, $items[0]['id']);
        $this->assertSame('target', $items[1]['type']);
        $this->assertSame(2, $items[1]['id']);
    }
}
