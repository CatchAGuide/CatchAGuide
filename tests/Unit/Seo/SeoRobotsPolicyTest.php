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

    public function test_empty_query_values_do_not_trigger_noindex(): void
    {
        $request = Request::create('/guidings', 'GET', ['place' => '']);
        $this->assertFalse($this->policy->shouldNoindexGuidings($request));
    }
}
