<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class SiteNavTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_guest_login_link_uses_flex_alignment_with_nav_actions(): void
    {
        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();

        $this->assertMatchesRegularExpression(
            '/class="cag-site-nav__login[^"]*d-md-inline-flex/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/class="cag-site-nav__login[^"]*\bd-md-inline"/',
            $html
        );
        $this->assertStringContainsString('cag-site-nav__cta', $html);
        $this->assertStringContainsString(__('homepage.header-login'), $html);
        $this->assertStringContainsString(__('homepage.filter-fishing-near-me'), $html);
        $this->assertStringContainsString(__('homepage.header-become-guide'), $html);
    }
}
