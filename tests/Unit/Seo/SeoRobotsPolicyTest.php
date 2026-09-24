<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\SeoRobotsPolicy;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class SeoRobotsPolicyTest extends TestCase
{
    private SeoRobotsPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new SeoRobotsPolicy();
    }

    public function test_clean_guidings_url_is_indexable(): void
    {
        $request = Request::create('/guidings', 'GET');
        $this->assertFalse($this->policy->shouldNoindexGuidings($request));
    }

    public function test_clean_guidings_alloffers_url_is_indexable(): void
    {
        $request = Request::create('/guidings/alloffers', 'GET');
        $this->assertFalse($this->policy->shouldNoindexGuidings($request));
    }

    public function test_guidings_filter_params_are_noindexed(): void
    {
        $request = Request::create('/guidings/alloffers', 'GET', ['place' => 'Berlin', 'sortby' => 'price']);
        $this->assertTrue($this->policy->shouldNoindexGuidings($request));
        $this->assertSame('NOINDEX, NOFOLLOW', $this->policy->robotsContentForGuidings($request));
    }

    public function test_guidings_species_filter_noindexes(): void
    {
        $request = Request::create('/guidings/alloffers', 'GET', ['species' => '1']);
        $this->assertTrue($this->policy->shouldNoindexGuidings($request));
    }

    public function test_guidings_num_guests_filter_noindexes(): void
    {
        $request = Request::create('/guidings/alloffers', 'GET', ['num_guests' => '4']);
        $this->assertTrue($this->policy->shouldNoindexGuidings($request));
    }

    public function test_clean_vacations_url_is_indexable(): void
    {
        $request = Request::create('/vacations', 'GET');
        $this->assertFalse($this->policy->shouldNoindexVacations($request));
    }

    public function test_vacation_filter_params_are_noindexed(): void
    {
        $request = Request::create('/vacations', 'GET', ['species' => 'pike', 'page' => '2']);
        $this->assertTrue($this->policy->shouldNoindexVacations($request));
    }

    public function test_plain_pagination_is_indexable_but_filtered_pagination_is_not(): void
    {
        $this->assertFalse($this->policy->shouldNoindexGuidings(Request::create('/guidings/alloffers', 'GET', ['page' => '3'])));
        $this->assertFalse($this->policy->shouldNoindexVacations(Request::create('/vacations/norwegen', 'GET', ['page' => '2'])));
        $this->assertTrue($this->policy->shouldNoindexGuidings(Request::create('/guidings/alloffers', 'GET', ['page' => '3', 'sortby' => 'price-asc'])));
    }

    public function test_empty_query_values_do_not_trigger_noindex(): void
    {
        $request = Request::create('/guidings', 'GET', ['place' => '']);
        $this->assertFalse($this->policy->shouldNoindexGuidings($request));
    }

    public function test_filters_missing_from_any_list_still_noindex(): void
    {
        foreach ([['target_fish' => ['1']], ['guide_id' => '5', 'page' => '2'], ['fishing_type' => '1'], ['duration' => '1'], ['country' => 'Deutschland']] as $query) {
            $this->assertTrue($this->policy->shouldNoindexGuidings(Request::create('/guidings', 'GET', $query)), json_encode($query));
        }
        $this->assertTrue($this->policy->shouldNoindexVacations(Request::create('/vacations/norwegen', 'GET', ['num_guests' => '2'])));
    }

    public function test_tracking_tags_and_empty_arrays_do_not_trigger_noindex(): void
    {
        $this->assertFalse($this->policy->shouldNoindexGuidings(Request::create('/guidings', 'GET', ['utm_source' => 'newsletter', 'gclid' => 'abc', 'page' => '2'])));
        $this->assertFalse($this->policy->shouldNoindexGuidings(Request::create('/guidings', 'GET', ['target_fish' => ['', null]])));
    }
}
