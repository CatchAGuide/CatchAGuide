<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LoginModalRedirectTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_get_login_redirects_to_welcome_with_modal_flag(): void
    {
        $response = $this->get(route('login'));

        $response->assertRedirect();
        $response->assertSessionHas('show_login_modal', true);

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('login=1', $location);
        $this->assertTrue(
            str_starts_with($location, route('welcome')) || str_contains($location, '://localhost/?login=1') || str_contains($location, '://localhost?login=1'),
            "Expected welcome landing, got: {$location}"
        );
    }

    public function test_get_login_from_public_page_keeps_that_page_with_modal(): void
    {
        $response = $this->from('/guidings')->get(route('login'));

        $response->assertRedirect();
        $response->assertSessionHas('show_login_modal', true);

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/guidings', $location);
        $this->assertStringContainsString('login=1', $location);
    }

    public function test_get_login_without_referer_lands_on_welcome(): void
    {
        $response = $this->get(route('login'));

        $response->assertRedirect();
        $response->assertSessionHas('show_login_modal', true);

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('login=1', $location);
        $this->assertStringNotContainsString('/guidings', $location);
    }

    public function test_get_login_does_not_render_dedicated_login_page(): void
    {
        $this->get(route('login'))
            ->assertRedirect()
            ->assertSessionHas('show_login_modal', true);
    }

    public function test_unauthenticated_protected_route_goes_through_login_entry(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect(route('login'));
    }

    public function test_ajax_logout_returns_redirect_with_login_query(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/')
            ->postJson(route('logout'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertStringContainsString('login=1', $response->json('redirect'));
        $this->assertGuest();
    }

    public function test_ajax_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $oldSessionId = session()->getId();

        $this->postJson(route('logout'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertGuest();
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_ajax_login_stays_on_current_page_without_profile_redirect(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->withSession(['url.intended' => route('profile.index')]);

        $response = $this->from('/guidings')->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'redirect' => null,
            ]);

        $this->assertAuthenticatedAs($user);
        $this->assertFalse(session()->has('url.intended'));
    }

    public function test_authenticated_visit_to_login_stays_on_previous_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/guidings')
            ->get(route('login'));

        $response->assertRedirect('/guidings');
    }
}
