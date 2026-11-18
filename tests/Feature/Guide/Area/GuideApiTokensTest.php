<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\GuideStatus;
use App\Models\User;
use App\Services\Guide\Area\GuideApiTokenService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

/**
 * POST /api/guide/tokens and DELETE /api/guide/tokens/current.
 */
class GuideApiTokensTest extends TestCase
{
    use DatabaseTransactions;

    private const PASSWORD = 'correct-horse-battery';

    protected function setUp(): void
    {
        parent::setUp();

        config(['guide_area.enabled' => true, 'guide_area.api.token_days' => 90]);
    }

    private function user(bool $guide = true, bool $beta = true): User
    {
        $user = User::factory()->create([
            'email' => 'api-token-'.uniqid().'@example.com',
            'password' => Hash::make(self::PASSWORD),
            'is_guide' => $guide ? 1 : 0,
            'guide_status' => $guide ? GuideStatus::VERIFIED : null,
            'language' => 'en',
        ]);
        $user->forceFill(['guide_area_beta' => $beta])->save();

        return $user;
    }

    private function signIn(User $user, string $password = self::PASSWORD)
    {
        return $this->postJson('/api/guide/tokens', [
            'email' => $user->email,
            'password' => $password,
            'device_name' => 'Test phone',
        ]);
    }

    public function test_a_guide_area_guide_gets_a_token_that_opens_the_api(): void
    {
        $guide = $this->user();

        $response = $this->signIn($guide)->assertCreated()->assertJsonPath('token_type', 'Bearer');
        $token = $response->json('token');

        $this->assertNotEmpty($token);
        $this->assertNotNull($response->json('expires_at'));
        $stored = PersonalAccessToken::findToken($token);
        $this->assertSame($guide->id, $stored->tokenable_id);
        $this->assertSame('guide-area:Test phone', $stored->name);
        $this->assertSame([GuideApiTokenService::ABILITY], $stored->abilities);

        $this->withToken($token)->getJson('/api/guide/tours')->assertOk();
    }

    public function test_signing_out_deletes_only_the_current_token(): void
    {
        $guide = $this->user();
        $first = $this->signIn($guide)->json('token');
        $second = $this->signIn($guide)->json('token');

        $this->withToken($first)->deleteJson('/api/guide/tokens/current')->assertNoContent();

        $this->assertNull(PersonalAccessToken::findToken($first));
        $this->assertNotNull(PersonalAccessToken::findToken($second));

        $this->app['auth']->forgetGuards();
        $this->withToken($first)->getJson('/api/guide/tours')->assertUnauthorized();
    }

    public function test_wrong_password_and_unknown_email_get_the_same_answer(): void
    {
        $guide = $this->user();

        $this->signIn($guide, 'wrong-password')->assertStatus(422)->assertJsonPath('message', __('auth.failed'));

        $this->postJson('/api/guide/tokens', [
            'email' => 'nobody-'.uniqid().'@example.com',
            'password' => self::PASSWORD,
            'device_name' => 'Test phone',
        ])->assertStatus(422)->assertJsonPath('message', __('auth.failed'));

        $this->assertSame(0, $guide->tokens()->count());
    }

    public function test_repeated_wrong_passwords_are_throttled(): void
    {
        $guide = $this->user();

        for ($i = 1; $i < 5; $i++) {
            $this->signIn($guide, 'wrong-'.$i)->assertStatus(422);
        }

        $this->signIn($guide, 'wrong-5')->assertStatus(429)->assertJsonStructure(['message', 'retry_after']);
        // Even the right password waits until the lockout ends.
        $this->signIn($guide)->assertStatus(429);
        $this->assertSame(0, $guide->tokens()->count());
    }

    public function test_guests_and_guides_without_the_guide_area_get_no_token(): void
    {
        $this->signIn($this->user(guide: false))
            ->assertForbidden()
            ->assertJsonPath('message', __('guide_area.api.not_a_guide'));

        $this->signIn($this->user(beta: false))
            ->assertForbidden()
            ->assertJsonPath('message', __('guide_area.api.not_enabled'));
    }

    public function test_the_request_is_validated(): void
    {
        $this->postJson('/api/guide/tokens', [])->assertStatus(422)->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    public function test_api_messages_follow_the_visitors_language(): void
    {
        $guide = $this->user(beta: false);

        $this->withSession(['locale' => 'en'])->actingAs($guide)
            ->getJson('/api/guide/tours', ['Referer' => 'http://localhost/'])
            ->assertForbidden()
            ->assertJsonPath('message', trans('guide_area.api.not_enabled', [], 'en'));

        $this->app['auth']->forgetGuards();
        $this->withSession(['locale' => 'de'])->actingAs($guide)
            ->getJson('/api/guide/tours', ['Referer' => 'http://localhost/'])
            ->assertForbidden()
            ->assertJsonPath('message', trans('guide_area.api.not_enabled', [], 'de'));
    }

    public function test_signing_out_needs_a_signed_in_guide(): void
    {
        $this->deleteJson('/api/guide/tokens/current')->assertUnauthorized();
    }
}
