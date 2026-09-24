<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\PaginationSeo;
use Illuminate\Http\Request;
use Tests\TestCase;

class PaginationSeoTest extends TestCase
{
    public function test_page_two_and_later_canonicalize_to_themselves(): void
    {
        $seo = new PaginationSeo();

        $this->assertSame(
            'https://catchaguide.de/destination/spanien?page=3',
            $seo->canonicalUrl(Request::create('https://catchaguide.de/destination/spanien', 'GET', ['page' => '3', 'sortby' => 'price-asc'])),
        );
    }

    public function test_page_one_and_invalid_pages_canonicalize_to_the_clean_url(): void
    {
        $seo = new PaginationSeo();

        foreach (['1', '0', '-2', 'abc', ''] as $page) {
            $this->assertSame(
                'https://catchaguide.de/offers',
                $seo->canonicalUrl(Request::create('https://catchaguide.de/offers', 'GET', ['page' => $page])),
                "page={$page}",
            );
        }
        $this->assertSame('https://catchaguide.de/offers', $seo->canonicalUrl(Request::create('https://catchaguide.de/offers?page[]=2')));
    }

    public function test_explicit_base_url_is_kept_and_title_gets_a_localized_suffix(): void
    {
        $seo = new PaginationSeo();
        $request = Request::create('https://catchaguide.de/guidings/alloffers', 'GET', ['page' => '2']);

        $this->assertSame('https://catchaguide.de/guidings/alloffers?page=2', $seo->canonicalUrl($request, 'https://catchaguide.de/guidings/alloffers'));

        app()->setLocale('de');
        $this->assertSame(' – Seite 2', $seo->titleSuffix($request));
        app()->setLocale('en');
        $this->assertSame(' – Page 2', $seo->titleSuffix($request));
        $this->assertSame('', $seo->titleSuffix(Request::create('https://catchaguide.de/offers')));
    }
}
