<?php

namespace Tests\Unit\Admin;

use App\Http\Requests\Admin\StoreMonthlyHighlightRequest;
use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\MonthlyHighlight;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MonthlyHighlightValidationTest extends TestCase
{
    public function test_store_request_rejects_more_than_three_items(): void
    {
        $countryIds = Country::query()->limit(2)->pluck('id');
        $targetIds = CategoryPage::query()->where('type', 'Targets')->limit(2)->pluck('id');
        if ($countryIds->count() < 2 || $targetIds->count() < 2) {
            $this->markTestSkipped('Need enough countries and targets.');
        }

        $request = StoreMonthlyHighlightRequest::create('/admin/monthly-highlights', 'POST', [
            'month' => 10,
            'title_en' => 'Too many',
            'title_de' => 'Zu viele',
            'country_ids' => [$countryIds[0], $countryIds[1]],
            'target_ids' => [$targetIds[0], $targetIds[1]],
            'is_active' => 1,
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));

        try {
            $request->validateResolved();
            $this->fail('Expected validation to fail for more than 3 items.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('items', $e->errors());
        }
    }

    public function test_store_request_builds_items_from_country_and_target_ids(): void
    {
        $country = Country::query()->first();
        $targetPage = CategoryPage::query()->where('type', 'Targets')->first();
        if (! $country || ! $targetPage) {
            $this->markTestSkipped('Need at least one country and target category page.');
        }

        $request = StoreMonthlyHighlightRequest::create('/admin/monthly-highlights', 'POST', [
            'month' => 3,
            'title_en' => 'March picks',
            'title_de' => 'März Tipps',
            'country_ids' => [$country->id],
            'target_ids' => [$targetPage->id],
            'is_active' => 1,
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        $validated = $request->validated();
        $this->assertCount(2, $validated['items']);
        $this->assertSame(MonthlyHighlight::ITEM_TYPE_COUNTRY, $validated['items'][0]['type']);
        $this->assertSame((int) $country->id, $validated['items'][0]['id']);
        $this->assertSame(MonthlyHighlight::ITEM_TYPE_TARGET, $validated['items'][1]['type']);
        $this->assertSame((int) $targetPage->id, $validated['items'][1]['id']);
    }

    public function test_model_normalizes_and_caps_items(): void
    {
        $highlight = new MonthlyHighlight([
            'items' => [
                ['type' => 'country', 'id' => 1],
                ['type' => 'target', 'id' => 2],
                ['type' => 'country', 'id' => 3],
                ['type' => 'target', 'id' => 4],
                ['type' => 'invalid', 'id' => 5],
            ],
        ]);

        $items = $highlight->normalizedItems();
        $this->assertCount(3, $items);
        $this->assertSame('country', $items[0]['type']);
        $this->assertSame(1, $items[0]['id']);
    }
}
