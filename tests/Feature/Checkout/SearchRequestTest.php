<?php

namespace Tests\Feature\Checkout;

use App\Http\Livewire\SearchRequest as SearchRequestComponent;
use App\Mail\GuidingRequestMail;
use App\Mail\SearchRequestUserMail;
use App\Models\EmailLog;
use App\Models\SearchRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class SearchRequestTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local', 'recaptcha.active' => false]);
        URL::forceRootUrl('http://cag.local');
        RateLimiter::clear('search-request:127.0.0.1');
    }

    public function test_search_request_page_renders(): void
    {
        $this->get(route('guidings.request'))->assertOk();
    }

    public function test_tour_search_request_can_be_completed_step_by_step(): void
    {
        $this->assertStepFlowSaves('tour', function ($component) {
            $component->set('budgetToSpend', 500)->call('next', null);
        });
    }

    public function test_holiday_search_request_can_be_completed_step_by_step(): void
    {
        $this->assertStepFlowSaves('holiday', function ($component) {
            $component->call('next', 'no')   // guided?
                ->call('next', 'no');        // boat rental?
        });
    }

    private function assertStepFlowSaves(string $fishingType, callable $remainingSteps): void
    {
        Mail::fake();
        // Both mails are deduplicated per address for 24h; clear the dev DB's admin log so it is sent here.
        EmailLog::query()->where('email', config('mail.admin_email'))->delete();
        $email = 'search-request-'.uniqid().'@example.com';

        $component = Livewire::test(SearchRequestComponent::class)
            ->call('next', $fishingType)
            ->set('country', 'Norway')
            ->set('region', 'Troms')
            ->call('next', null)
            ->set('target_fish', 'Cod')
            ->set('number_of_guest', 2)
            ->call('next', null)
            ->set('date_from', now()->addMonth()->toDateString())
            ->set('date_to', now()->addMonth()->addDays(3)->toDateString())
            ->call('next', null);

        $remainingSteps($component);

        $component->set('name', 'Test Guest')
            ->set('email', $email)
            ->set('phone', '49123456789')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('request.thank-you'));

        $request = SearchRequest::query()->where('email', $email)->firstOrFail();
        $this->assertSame('Norway', $request->country);
        $this->assertSame(2, (int) $request->number_of_guest);

        Mail::assertSent(SearchRequestUserMail::class, fn ($mail) => $mail->hasTo($email) && $mail->render() !== '');
        Mail::assertSent(GuidingRequestMail::class, fn ($mail) => $mail->hasTo(config('mail.admin_email')) && str_contains($mail->render(), 'Norway'));
    }

    public function test_search_request_requires_contact_details(): void
    {
        Mail::fake();

        Livewire::test(SearchRequestComponent::class)
            ->call('save')
            ->assertHasErrors(['name', 'email', 'phone']);

        Mail::assertNothingSent();
    }

    public function test_thank_you_page_renders(): void
    {
        $this->get(route('request.thank-you'))->assertOk();
    }
}
