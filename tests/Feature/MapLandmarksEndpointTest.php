<?php

namespace Tests\Feature;

use App\Http\Controllers\MapLandmarkController;
use App\Services\Maps\LandmarkService;
use Illuminate\Http\Request;
use Tests\TestCase;

class MapLandmarksEndpointTest extends TestCase
{
    public function test_landmarks_endpoint_validates_bounds(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $request = Request::create('/maps/landmarks', 'GET', [
            'sw_lat' => 'bad',
            'sw_lng' => 11.5,
            'ne_lat' => 48.2,
            'ne_lng' => 11.7,
            'zoom' => 12,
        ]);

        $controller = new MapLandmarkController();
        $controller($request, app(LandmarkService::class));
    }

    public function test_landmarks_endpoint_returns_json_payload(): void
    {
        config([
            'services.maps.landmarks.enabled' => true,
            'services.maps.landmarks.min_zoom' => 10,
        ]);

        $request = Request::create('/maps/landmarks', 'GET', [
            'sw_lat' => 48.10,
            'sw_lng' => 11.50,
            'ne_lat' => 48.20,
            'ne_lng' => 11.70,
            'zoom' => 8,
        ]);

        $controller = new MapLandmarkController();
        $response = $controller($request, app(LandmarkService::class));

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertArrayHasKey('landmarks', $data);
        $this->assertSame([], $data['landmarks']);
    }

    public function test_landmarks_route_is_registered(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('maps.landmarks'),
            'Expected maps.landmarks named route to be registered'
        );
    }
}
