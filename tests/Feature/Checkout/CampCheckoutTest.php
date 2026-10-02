<?php

namespace Tests\Feature\Checkout;

use App\Mail\Admin\CampCheckoutAdminMail;
use App\Mail\Guest\CampCheckoutGuestMail;
use App\Mail\VacationBookingAdminMail;
use App\Mail\VacationBookingCustomerMail;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\RentalBoat;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CampCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local', 'recaptcha.active' => false]);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_checkout_page_renders_both_layouts_with_the_camp_options(): void
    {
        [$camp, $accommodation, $boat] = $this->makeCampWithOptions();

        $response = $this->get(route('checkout.camp.show', [
            'slug' => $camp->slug,
            'date' => now()->addDays(10)->toDateString(),
            'persons' => 3,
        ]));

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex,nofollow">', false);
        $response->assertSee('x-data="campCheckout"', false);
        $response->assertSee('class="cc-aside"', false);
        $response->assertSee('class="cc-summary__line-label"', false);
        $response->assertSee('class="cc-summary__line-detail"', false);
        $response->assertSee('class="cc-summary__line-amount"', false);
        $response->assertSee('class="cc-summary__total-label"', false);
        $response->assertSee('class="cc-bar"', false);
        $response->assertSee('class="cc-dock"', false);

        $html = $response->getContent();
        $form = strpos($html, 'class="cc-form"');
        $formEnd = strpos($html, '</form>', $form);
        $summary = strpos($html, 'cc-summary--inline');
        $aside = strpos($html, 'class="cc-aside"');
        $captcha = strpos($html, 'class="cc-captcha"');
        $cta = strpos($html, 'class="cc-cta"');
        $dock = strpos($html, 'class="cc-dock"');
        $bar = strpos($html, 'class="cc-bar"');
        $this->assertNotFalse($form);
        $this->assertNotFalse($formEnd);
        $this->assertNotFalse($summary);
        $this->assertNotFalse($aside);
        $this->assertNotFalse($captcha);
        $this->assertNotFalse($cta);
        $this->assertNotFalse($dock);
        $this->assertNotFalse($bar);
        // Price overview stays in the form. The reCAPTCHA is in the summary card, above its submit button.
        $this->assertLessThan($summary, $form);
        $this->assertLessThan($formEnd, $summary);
        $this->assertLessThan($captcha, $formEnd);
        $this->assertLessThan($captcha, $aside);
        $this->assertLessThan($cta, $captcha);
        $this->assertLessThan($dock, $cta);
        $this->assertLessThan($bar, $dock);
        // Mobile bar drops the generic error when the captcha message is already shown.
        // The desktop submit block keeps its own alert.
        $this->assertStringContainsString('x-show="hasErrors && !errors.captcha"', $html);
        $this->assertStringContainsString('class="cc-cta__alert" x-show="hasErrors"', $html);
        $response->assertSee('id="cc-boat"', false);
        $response->assertDontSee('id="cc-tour"', false);
        $response->assertSee(__('checkout.camp.submit'));
        $response->assertSee('class="cc-date"', false);
        $response->assertDontSee('class="cc-date is-empty"', false);
        $response->assertSee('cc-date__glyph', false);
        $response->assertSee('openArrivalPicker', false);
        $response->assertSee(__('checkout.camp.arrival_placeholder'), false);
        $response->assertSee(
            __('checkout.camp.arrival').' <span class="cc-field__optional">'.__('checkout.camp.optional').'</span>',
            false,
        );
        preg_match('/<input[^>]*id="cc-date"[^>]*>/', $html, $dateInput);
        $this->assertArrayHasKey(0, $dateInput);
        $this->assertDoesNotMatchRegularExpression('/\brequired\b/', $dateInput[0]);

        $config = $this->clientConfig($response->getContent());
        $this->assertSame(3, $config['persons']);
        $this->assertSame(now()->addDays(10)->toDateString(), $config['arrivalDate']);
        $this->assertSame($accommodation->id, $config['accommodationId']);
        $this->assertSame($boat->id, $config['pricing']['boats'][0]['id']);
        $this->assertSame(route('checkout.camp.store', $camp->slug), $config['submitUrl']);
    }

    public function test_checkout_page_ignores_invalid_prefill_values(): void
    {
        [$camp] = $this->makeCampWithOptions();

        $response = $this->get(route('checkout.camp.show', [
            'slug' => $camp->slug,
            'date' => '2001-01-01',
            'persons' => 999,
            'nights' => -4,
        ]));

        $config = $this->clientConfig($response->getContent());
        $this->assertNull($config['arrivalDate']);
        $response->assertSee('class="cc-date is-empty"', false);
        $response->assertSee(__('checkout.camp.arrival_placeholder'), false);
        $this->assertSame(20, $config['persons']);
        $this->assertSame(2, $config['nights']); // accommodation minimum stay
    }

    public function test_draft_camp_checkout_redirects_to_the_camp_catalog(): void
    {
        [$camp] = $this->makeCampWithOptions(['status' => 'draft']);

        $this->get(route('checkout.camp.show', $camp->slug))
            ->assertRedirect(route('vacations.camps.index'));
    }

    public function test_submission_stores_a_camp_request_with_a_server_side_estimate(): void
    {
        [$camp, $accommodation, $boat] = $this->makeCampWithOptions();
        $arrival = now()->addDays(20)->toDateString();

        $response = $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'arrival_date' => $arrival,
            'nights' => 3,
            'persons' => 2,
            'accommodation_id' => $accommodation->id,
            'rental_boat_id' => $boat->id,
            'message' => 'We bring our own rods.',
            // Anything price-like from the client is ignored.
            'estimated_total' => 1,
            'total' => 1,
        ]));

        $response->assertOk()->assertJson(['success' => true]);

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();
        $this->assertSame(CampVacationBooking::SOURCE_CAMP, $booking->source_type);
        $this->assertSame($camp->id, (int) $booking->source_id);
        $this->assertSame($arrival, $booking->preferred_date->toDateString());
        $this->assertSame(3, $booking->nights);
        $this->assertSame(2, $booking->number_of_persons);
        $this->assertSame($accommodation->id, (int) $booking->accommodation_id);
        $this->assertSame($boat->id, (int) $booking->rental_boat_id);
        // 3 × 120 (2-guest tier) + 3 × 200 (boat per day)
        $this->assertSame('960.00', (string) $booking->estimated_total);
        $this->assertSame('Anna Fischer', $booking->name);
        $this->assertSame(CampVacationBooking::STATUS_OPEN, $booking->status);
        $this->assertStringContainsString('We bring our own rods.', $booking->message);
        $this->assertStringContainsString(__('checkout.camp.summary.arrival').':', $booking->message);
        $this->assertCount(2, $booking->price_breakdown);

        $response->assertJson(['redirect_url' => route('checkout.camp.thank-you', [$camp->slug, $booking->id])]);
    }

    public function test_submission_accepts_a_request_without_an_arrival_date(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $payload = $this->payload([
            'accommodation_id' => $accommodation->id,
            'arrival_date' => '',
        ]);

        $this->postJson(route('checkout.camp.store', $camp->slug), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();
        $this->assertNull($booking->preferred_date);
        $this->assertStringNotContainsString(__('checkout.camp.summary.arrival').':', $booking->message);

        $this->get(route('checkout.camp.thank-you', [$camp->slug, $booking->id]))
            ->assertOk()
            ->assertSee(__('checkout.camp.success_text_no_date', [
                'email' => '<strong>'.e($booking->email).'</strong>',
            ]), false);
    }

    public function test_submission_mails_the_guest_the_camp_confirmation_in_the_page_language(): void
    {
        Mail::fake();
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $this->withSession(['locale' => 'de'])
            ->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
                'accommodation_id' => $accommodation->id,
            ]))
            ->assertOk();

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();
        $this->assertSame('de', $booking->language);

        Mail::assertSent(CampCheckoutGuestMail::class, fn (CampCheckoutGuestMail $mail) => $mail->hasTo('anna@example.com')
            && $mail->locale === 'de'
            && $mail->booking->is($booking));
        Mail::assertSent(CampCheckoutAdminMail::class, fn (CampCheckoutAdminMail $mail) => $mail->hasTo(config('mail.admin_email'))
            && $mail->locale === 'de'
            && $mail->booking->is($booking));
        Mail::assertNotSent(VacationBookingAdminMail::class);
        Mail::assertNotSent(VacationBookingCustomerMail::class);
    }

    public function test_guest_mail_uses_the_signed_in_users_own_language_over_the_page_language(): void
    {
        Mail::fake();
        [$camp, $accommodation] = $this->makeCampWithOptions();
        $user = User::query()->firstOrFail();
        $user->forceFill(['language' => 'en'])->save();

        $this->actingAs($user)
            ->withSession(['locale' => 'de'])
            ->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
                'accommodation_id' => $accommodation->id,
            ]))
            ->assertOk();

        Mail::assertSent(CampCheckoutGuestMail::class, fn (CampCheckoutGuestMail $mail) => $mail->locale === 'en');
        Mail::assertSent(CampCheckoutAdminMail::class, fn (CampCheckoutAdminMail $mail) => $mail->locale === 'en');
    }

    public function test_guest_mail_locale_falls_back_to_the_page_language(): void
    {
        $user = (new User)->forceFill(['language' => 'Deutsch']);
        $booking = new CampVacationBooking(['language' => 'en']);

        $booking->setRelation('user', $user);
        $this->assertSame('de', $booking->customerLocale());

        $user->language = 'fr';
        $this->assertSame('en', $booking->customerLocale());

        $booking->setRelation('user', null);
        $this->assertSame('en', $booking->customerLocale());
    }

    public function test_guest_mail_renders_the_design_in_german_and_english(): void
    {
        [$camp, $accommodation, $boat] = $this->makeCampWithOptions();

        $booking = CampVacationBooking::query()->create([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => $camp->id,
            'preferred_date' => '2026-10-12',
            'nights' => 3,
            'number_of_persons' => 2,
            'estimated_total' => 960,
            'currency' => 'EUR',
            'price_breakdown' => [
                ['type' => 'accommodation', 'id' => $accommodation->id, 'name' => 'Apartment', 'quantity' => 3, 'unit_price' => 120.0, 'amount' => 360.0],
                ['type' => 'boat', 'id' => $boat->id, 'name' => 'Boat', 'quantity' => 3, 'unit_price' => 200.0, 'amount' => 600.0],
            ],
            'name' => 'Anna Fischer',
            'first_name' => 'Anna',
            'last_name' => 'Fischer',
            'email' => 'anna@example.com',
            'phone_country_code' => '+49',
            'phone' => '151 2345678',
            'message' => 'Summary',
            'language' => 'de',
            'status' => CampVacationBooking::STATUS_OPEN,
        ]);

        $german = (new CampCheckoutGuestMail($booking, $camp, 'Wir bringen eigenes Tackle mit.'))->locale('de');
        $html = $german->render();

        $this->assertSame('Deine Anfrage für Test Checkout Camp ist angekommen', $german->subject);
        $this->assertStringContainsString('Hallo Anna,', $html);
        $this->assertStringContainsString('Riba-Roja · Spain', $html);
        $this->assertStringContainsString('Mo., 12.10.2026', $html);
        $this->assertStringContainsString('Mietboot · 3 Tage × 200', $html);
        $this->assertStringContainsString('ca. 960', $html);
        $this->assertStringContainsString('„Wir bringen eigenes Tackle mit.“', $html);
        $this->assertStringContainsString('So geht es weiter', $html);
        $this->assertStringContainsString('href="'.route('vacations.camps.show', $camp->slug).'"', $html);

        $english = (new CampCheckoutGuestMail($booking, $camp))->locale('en');
        $html = $english->render();

        $this->assertSame('Your request for Test Checkout Camp has arrived', $english->subject);
        $this->assertStringContainsString('Hi Anna,', $html);
        $this->assertStringContainsString('Mon, Oct 12, 2026', $html);
        $this->assertStringContainsString('approx. €960', $html);
        $this->assertStringContainsString('What happens next', $html);
        // No message section when the guest left the message empty.
        $this->assertStringNotContainsString(__('emails.camp_checkout_guest.section_message', [], 'en'), $html);
    }

    public function test_guest_mail_is_listed_and_previewable_in_the_admin_email_templates(): void
    {
        $template = config('email_templates.templates.guest_camp_checkout_request');
        $this->assertSame('mails.guest.camp_checkout_request', $template['view']);
        $this->assertSame((new CampCheckoutGuestMail(new CampVacationBooking, new Camp))->type, $template['log_type']);

        // Same steps as GuidingsSettingController::emailPreview().
        foreach (['de' => 'So geht es weiter', 'en' => 'What happens next'] as $locale => $heading) {
            app()->setLocale($locale);
            $html = view($template['view'], CampCheckoutGuestMail::sample()->viewData())->render();

            $this->assertStringContainsString('Welscamp Riba-Roja', $html);
            $this->assertStringContainsString($heading, $html);
        }
    }

    public function test_admin_mail_renders_the_design_in_german_and_english(): void
    {
        [$camp, $accommodation, $boat] = $this->makeCampWithOptions();

        $booking = CampVacationBooking::query()->create([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => $camp->id,
            'preferred_date' => '2026-10-12',
            'nights' => 3,
            'number_of_persons' => 2,
            'estimated_total' => 960,
            'currency' => 'EUR',
            'price_breakdown' => [
                ['type' => 'accommodation', 'id' => $accommodation->id, 'name' => 'Apartment', 'quantity' => 3, 'unit_price' => 120.0, 'amount' => 360.0],
                ['type' => 'boat', 'id' => $boat->id, 'name' => 'Aluboot 5 m', 'quantity' => 3, 'unit_price' => 200.0, 'amount' => 600.0],
            ],
            'name' => 'Anna Fischer',
            'first_name' => 'Anna',
            'last_name' => 'Fischer',
            'email' => 'anna@example.com',
            'phone_country_code' => '+49',
            'phone' => '151 2345678',
            'message' => 'Summary',
            'language' => 'de',
            'status' => CampVacationBooking::STATUS_OPEN,
        ]);
        $booking->forceFill(['created_at' => '2026-10-01 14:32:00']);

        $german = (new CampCheckoutAdminMail($booking, $camp, 'Wir bringen eigenes Tackle mit.'))->locale('de');
        $html = $german->render();

        $this->assertSame("Neue Camp-Anfrage · Test Checkout Camp · 12.10. · 2 Pers. · ca. 960\u{00A0}€", $german->subject);
        $this->assertTrue($german->hasReplyTo('anna@example.com'));
        $this->assertStringContainsString('Angelcamp', $html);
        $this->assertStringContainsString('Hallo Team,', $html);
        $this->assertStringContainsString('<strong style="font-weight:600;">Fr., 02.10.2026, 14:32</strong>', $html);
        $this->assertStringContainsString('href="mailto:anna@example.com"', $html);
        $this->assertStringContainsString('href="tel:+491512345678"', $html);
        $this->assertStringContainsString('Riba-Roja · Spain', $html);
        $this->assertStringContainsString('Mo., 12.10.2026', $html);
        $this->assertStringContainsString('Do., 15.10.2026', $html);
        $this->assertStringContainsString('Aluboot 5 m', $html);
        $this->assertStringContainsString('Mietboot · 3 Tage × 200', $html);
        $this->assertStringContainsString('„Wir bringen eigenes Tackle mit.“', $html);
        $this->assertStringContainsString('href="'.route('admin.camp-vacation-bookings.index').'"', $html);
        $this->assertStringContainsString('href="'.route('vacations.camps.show', $camp->slug).'"', $html);

        $english = (new CampCheckoutAdminMail($booking, $camp))->locale('en');
        $html = $english->render();

        $this->assertSame('New camp request · Test Checkout Camp · Oct 12 · 2 pers. · approx. €960', $english->subject);
        $this->assertStringContainsString('Hi team,', $html);
        $this->assertStringContainsString('Fri, Oct 2, 2026, 14:32', $html);
        $this->assertStringContainsString('Rental boat', $html);
        $this->assertStringContainsString('Thu, Oct 15, 2026', $html);
        // No message section when the guest left the message empty.
        $this->assertStringNotContainsString(__('emails.checkout_request_admin.section_message', [], 'en'), $html);
    }

    public function test_admin_mail_is_listed_and_previewable_in_the_admin_email_templates(): void
    {
        $template = config('email_templates.templates.admin_camp_checkout_request');
        $this->assertSame('mails.admin.checkout_request', $template['view']);
        $this->assertSame((new CampCheckoutAdminMail(new CampVacationBooking, new Camp))->type, $template['log_type']);

        // Same steps as GuidingsSettingController::emailPreview().
        foreach (['de' => 'Neue Anfrage', 'en' => 'New request'] as $locale => $heading) {
            app()->setLocale($locale);
            $html = view($template['view'], CampCheckoutAdminMail::sample()->viewData())->render();

            $this->assertStringContainsString('Welscamp Riba-Roja', $html);
            $this->assertStringContainsString($heading, $html);
        }
    }

    public function test_submission_rejects_options_from_another_camp(): void
    {
        [$camp] = $this->makeCampWithOptions();
        [, $foreignAccommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $foreignAccommodation->id,
        ]))->assertStatus(422)->assertJsonValidationErrors(['accommodation_id']);
    }

    public function test_submission_enforces_the_accommodation_minimum_stay(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
            'nights' => 1,
        ]))->assertStatus(422)->assertJsonValidationErrors(['nights']);
    }

    public function test_submission_validates_date_and_contact(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), [
            'arrival_date' => now()->subDay()->toDateString(),
            'nights' => 3,
            'persons' => 2,
            'accommodation_id' => $accommodation->id,
            'email' => 'not-an-email',
            'country_code' => '+49',
            'phone' => 'abc',
        ])->assertStatus(422)->assertJsonValidationErrors(['arrival_date', 'first_name', 'last_name', 'email', 'phone']);
    }

    public function test_submission_for_a_draft_camp_is_refused(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions(['status' => 'draft']);

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
        ]))->assertStatus(410)->assertJson(['success' => false]);

        $this->assertSame(0, CampVacationBooking::query()->where('source_id', $camp->id)->count());
    }

    public function test_thank_you_page_is_only_visible_to_the_session_that_sent_the_request(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
        ]))->assertOk();

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();
        $url = route('checkout.camp.thank-you', [$camp->slug, $booking->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee(__('checkout.camp.success_title'))
            ->assertSee('anna@example.com');

        // A fresh session (someone guessing ids) gets a 404, as does a mismatched camp slug.
        $this->flushSession();
        $this->get($url)->assertNotFound();
    }

    public function test_thank_you_page_404s_for_a_different_camp_slug(): void
    {
        [$camp, $accommodation] = $this->makeCampWithOptions();
        [$otherCamp] = $this->makeCampWithOptions();

        $this->postJson(route('checkout.camp.store', $camp->slug), $this->payload([
            'accommodation_id' => $accommodation->id,
        ]))->assertOk();

        $booking = CampVacationBooking::query()->latest('id')->firstOrFail();

        $this->get(route('checkout.camp.thank-you', [$otherCamp->slug, $booking->id]))->assertNotFound();
    }

    public function test_camp_page_booking_ctas_lead_to_the_checkout(): void
    {
        [$camp] = $this->makeCampWithOptions();

        $html = $this->get(route('vacations.camps.show', $camp->slug))->assertOk()->getContent();

        // @json escapes slashes.
        $this->assertStringContainsString(str_replace('/', '\/', route('checkout.camp.show', $camp->slug)), $html);
        $this->assertStringNotContainsString('id="contactModal"', $html);
        $this->assertStringContainsString('id="campGeneralContactModal"', $html);
    }

    public function test_checkout_lives_under_the_checkout_prefix_and_old_urls_redirect(): void
    {
        [$camp] = $this->makeCampWithOptions();

        $this->assertSame('/checkout/camps/'.$camp->slug, route('checkout.camp.show', $camp->slug, false));
        $this->assertSame('/checkout/camps/'.$camp->slug.'/thank-you/5', route('checkout.camp.thank-you', [$camp->slug, 5], false));

        $this->get('/vacations/camps/'.$camp->slug.'/checkout?persons=3&nights=4')
            ->assertStatus(301)
            ->assertRedirect(route('checkout.camp.show', ['slug' => $camp->slug, 'persons' => 3, 'nights' => 4]));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'arrival_date' => now()->addDays(14)->toDateString(),
            'nights' => 3,
            'persons' => 2,
            'first_name' => 'Anna',
            'last_name' => 'Fischer',
            'email' => 'anna@example.com',
            'country_code' => '+49',
            'phone' => '151 2345678',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function clientConfig(string $html): array
    {
        $this->assertMatchesRegularExpression('/<script type="application\/json" id="camp-checkout-config">(.*?)<\/script>/s', $html);
        preg_match('/<script type="application\/json" id="camp-checkout-config">(.*?)<\/script>/s', $html, $match);

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{0: Camp, 1: Accommodation, 2: RentalBoat}
     */
    private function makeCampWithOptions(array $overrides = []): array
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        $camp = Camp::query()->create(array_merge([
            'title' => 'Test Checkout Camp',
            'slug' => 'test-checkout-camp-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'city' => 'Riba-Roja',
            'region' => 'Catalonia',
            'country' => 'Spain',
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides));

        $accommodation = Accommodation::query()->create([
            'status' => 'active',
            'user_id' => $user->id,
            'title' => 'Test Apartment '.uniqid(),
            'slug' => 'test-apartment-'.uniqid(),
            'location' => 'Test Location',
            'city' => 'Riba-Roja',
            'country' => 'Spain',
            'region' => 'Catalonia',
            'accommodation_type' => 'apartment',
            'max_occupancy' => 4,
            'minimum_stay_nights' => 2,
            'per_person_pricing' => [
                'tier_1' => ['person_count' => 1, 'price_per_night' => 100, 'price_per_week' => null],
                'tier_2' => ['person_count' => 2, 'price_per_night' => 120, 'price_per_week' => null],
            ],
        ]);
        $camp->accommodations()->attach($accommodation->id);

        $boat = RentalBoat::query()->create([
            'status' => 'active',
            'user_id' => $user->id,
            'title' => 'Test Boat '.uniqid(),
            'slug' => 'test-boat-'.uniqid(),
            'location' => 'Test Location',
            'city' => 'Riba-Roja',
            'country' => 'Spain',
            'region' => 'Catalonia',
            'boat_type' => 'aluminium',
            'desc_of_boat' => 'Aluminium boat',
            'max_persons' => 3,
            'price_type' => 'per_day',
            'prices' => ['per_day' => 200, 'per_week' => 1100],
        ]);
        $camp->rentalBoats()->attach($boat->id);

        return [$camp, $accommodation, $boat];
    }
}
