<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\CountrySlug;
use App\Services\Vacation\VacationRedirectResolver;
use Illuminate\Http\Request;
use Tests\TestCase;

class VacationRedirectResolverTest extends TestCase
{
    public function test_trips_legacy_path_canonicalizes_in_one_hop(): void
    {
        $resolver = $this->app->make(VacationRedirectResolver::class);
        $request = Request::create('/trips/Costa_Rica', 'GET');

        $target = $resolver->resolve($request);

        $this->assertSame('/vacations/trips/costa-rica', $target);
    }

    public function test_country_slug_canonicalize_helper(): void
    {
        $this->assertSame('österreich', CountrySlug::canonicalize('Österreich'));
        $this->assertSame('costa-rica', CountrySlug::canonicalize('Costa Rica'));
    }
}
