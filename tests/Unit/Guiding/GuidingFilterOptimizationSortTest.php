<?php

namespace Tests\Unit\Guiding;

use App\Models\Guiding;
use App\Traits\GuidingFilterOptimization;
use Illuminate\Http\Request;
use Tests\TestCase;

class GuidingFilterOptimizationSortTest extends TestCase
{
    use GuidingFilterOptimization;

    public function test_recommended_sort_orders_by_guide_rating_then_review_count_then_newest(): void
    {
        $query = Guiding::query();
        $request = Request::create('/guidings/alloffers', 'GET', ['sortby' => 'recommended']);

        $this->applySorting($query, $request, 1234);

        $sql = $this->normalizedSql($query);

        $this->assertStringContainsString(
            'order by (select avg(grandtotal_score) from reviews where reviews.guide_id = guidings.user_id) desc, '.
            '(select count(*) from reviews where reviews.guide_id = guidings.user_id) desc, '.
            'created_at desc',
            $sql
        );
    }

    public function test_newest_sort_is_unaffected_by_recommended_change(): void
    {
        $query = Guiding::query();
        $request = Request::create('/guidings/alloffers', 'GET', ['sortby' => 'newest']);

        $this->applySorting($query, $request, 1234);

        $sql = $this->normalizedSql($query);

        $this->assertStringContainsString('order by created_at desc', $sql);
        $this->assertStringNotContainsString('grandtotal_score', $sql);
    }

    private function normalizedSql($query): string
    {
        return strtolower(str_replace(['`', '"'], '', $query->toSql()));
    }
}
