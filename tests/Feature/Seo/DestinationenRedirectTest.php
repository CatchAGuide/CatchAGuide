<?php

namespace Tests\Feature\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DestinationenRedirectTest extends TestCase
{
    public function test_destinationen_route_is_registered(): void
    {
        $this->assertTrue(Route::has('destination_de'));
    }

    public function test_destinationen_permanently_redirects_to_destination(): void
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(Request::create('/destinationen', 'GET'));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('/destination', $response->headers->get('Location'));
    }
}
