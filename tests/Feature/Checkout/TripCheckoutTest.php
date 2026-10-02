<?php

namespace Tests\Feature\Checkout;

use App\Mail\Admin\TripCheckoutAdminMail;
use App\Mail\Guest\TripCheckoutGuestMail;
use App\Mail\VacationBookingAdminMail;
use App\Mail\VacationBookingCustomerMail;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TripCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local', 'recaptcha.active' => false]);
        URL::forceRootUrl('http://cag.local');
        app()->setLocale('de');
    }

    public function test_dated_trip_checkout_renders_the_departure_dropdown_in_both_layouts(): void
    {
        $trip = $this->makeTrip();
        $first = now()->addDays(10)->toDateString();
        $this->addDeparture($trip, $first, 4);
        $this->addDeparture($trip, now()->addDays(30)->toDateString(), 1);
        $this->addDeparture($trip, now()->addDays(40)->toDateString(), 0); // fully booked
        $this->addDeparture($trip, now()->subDays(5)->toDateString(), 5); // passed

        $response = $this->get(route('checkout.trip.show', ['slug' => $trip->slug, 'date' => $first, 'persons' => 3]));

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex,nofollow">', false);
        $response->assertSee('x-data="tripCheckout"', false);
        $response->assertSee('class="cc-departures"', false);
        $response->assertDontSee('class="cc-wish"', false);
        $response->assertSee('class="cc-aside"', false);
        $response->assertSee('class="cc-dock"', false);
        $response->assertSee(__('checkout.trip.submit'));
        $response->assertSee(__('checkout.trip.message'));
        $response->assertSee('Pyrenäen · Spanien · 7 Tage · 6 Nächte');
        $response->assertSee(trans_choice('checkout.trip.spots_left', 1, ['count' => 1]));

        $html = $response->getContent();
        $form = strpos($html, 'class="cc-form"');
        $formEnd = strpos($html, '</form>', $form);
        $summary = strpos($html, 'cc-summary--inline');
        $captcha = strpos($html, 'class="cc-captcha"');
        $cta = strpos($html, 'class="cc-cta"');
        $dock = strpos($html, 'class="cc-dock"');
        // Price overview stays in the form; the reCAPTCHA sits in the aside above its submit button.
        $this->assertLessThan($summary, $form);
        $this->assertLessThan($formEnd, $summary);
        $this->assertLessThan($captcha, $formEnd);
        $this->assertLessThan($cta, $captcha);
        $this->assertLessThan($dock, $cta);
        // Only the two open, upcoming departures are offered.
        $this->assertSame(2, substr_count($html, 'class="cc-departures__option"'));

        $config = $this->clientConfig($html);
        $this->assertTrue($config['fixedDates']);
        $this->assertSame($first, $config['departureDate']);
        $this->assertSame(3, $config['persons']);
        $this->assertSame(6, $config['maxPersons']);
        $this->assertEquals(1290, $config['pricePerPerson']);
        $this->assertSame([$first, now()->addDays(30)->toDateString()], array_column($config['departures'], 'date'));
        $this->assertSame(route('checkout.trip.store', $trip->slug), $config['submitUrl']);
    }

    public function test_trip_without_open_departures_asks_for_a_preferred_window(): void
    {
        $trip = $this->makeTrip();
        $this->addDeparture($trip, now()->subDays(3)->toDateString(), 4);
        $this->addDeparture($trip, now()->addDays(20)->toDateString(), 0);

        $response = $this->get(route('checkout.trip.show', $trip->slug))->assertOk();

        $response->assertSee('class="cc-wish"', false);
        $response->assertDontSee('class="cc-departures"', false);
        $response->assertSee(__('checkout.trip.no_fixed_dates'));
        $response->assertSee(__('checkout.trip.duration', ['duration' => '7 Tage · 6 Nächte']));
        $this->assertFalse($this->clientConfig($response->getContent())['fixedDates']);
    }

    public function test_year_round_trip_asks_for_a_preferred_window(): void
    {
        $trip = $this->makeTrip(['year_round_availability' => true]);
        $this->addDeparture($trip, now()->addDays(10)->toDateString(), 4);

        $response = $this->get(route('checkout.trip.show', $trip->slug))->assertOk();

        $response->assertSee('class="cc-wish"', false);
        $this->assertFalse($this->clientConfig($response->getContent())['fixedDates']);
    }

    public function test_checkout_page_ignores_invalid_prefill_values(): void
    {
        $trip = $this->makeTrip();
        $this->addDeparture($trip, now()->addDays(10)->toDateString(), 4);

        $response = $this->get(route('checkout.trip.show', [
            'slug' => $trip->slug,
            'date' => now()->addDays(11)->toDateString(),
            'persons' => 999,
        ]));

        $config = $this->clientConfig($response->getContent());
        $this->assertNull($config['departureDate']);
        $this->assertSame(6, $config['persons']); // group_size_max
        $response->assertSee(__('checkout.trip.choose_date'));
    }

    public function test_draft_trip_checkout_redirects_to_the_trip_catalog(): void
    {
        $trip = $this->makeTrip(['status' => 'draft']);

        $this->get(route('checkout.trip.show', $trip->slug))
            ->assertRedirect(route('vacations.trips.index'));
    }

    public function test_submission_for_a_departure_stores_a_trip_request_with_a_server_side_estimate(): void
    {
        $trip = $this->makeTrip();
        $departure = now()->addDays(10)->toDateString();
        $this->addDeparture($trip, $departure, 4);

        $response = $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload([
            'departure_date' => $departure,
            'persons' => 2,
            'message' => 'Eigene Fliegenrute vorhanden.',
            // Client prices are ignored.
            'estimated_total' => 1,
        ]));

        $booking = TripBooking::query()->where('source_id', $trip->id)->latest('id')->firstOrFail();
        $response->assertOk()->assertJson([
            'success' => true,
            'redirect_url' => route('checkout.trip.thank-you', [$trip->slug, $booking->id]),
        ]);

        $this->assertSame(TripBooking::SOURCE_TRIP, $booking->source_type);
        $this->assertSame($departure, $booking->preferred_date->toDateString());
        $this->assertNull($booking->preferred_date_to);
        $this->assertSame(2, $booking->number_of_persons);
        $this->assertEquals(2580, $booking->estimated_total);
        $this->assertSame('EUR', $booking->currency);
        $this->assertSame('Anna Fischer', $booking->name);
        $this->assertSame('Anna', $booking->first_name);
        $this->assertSame('de', $booking->language);
        $this->assertSame(TripBooking::STATUS_OPEN, $booking->status);
        $this->assertStringContainsString(__('checkout.trip.summary.heading'), $booking->message);
        $this->assertStringContainsString('ca. 2.580', $booking->message);
        $this->assertStringContainsString('Eigene Fliegenrute vorhanden.', $booking->message);
    }

    public function test_submission_for_a_preferred_window_stores_the_range(): void
    {
        $trip = $this->makeTrip(['year_round_availability' => true]);
        $start = now()->addDays(30)->toDateString();
        $end = now()->addDays(60)->toDateString();

        $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload([
            'wish_start' => $start,
            'wish_end' => $end,
            'persons' => 3,
        ]))->assertOk();

        $booking = TripBooking::query()->where('source_id', $trip->id)->latest('id')->firstOrFail();
        $this->assertSame($start, $booking->preferred_date->toDateString());
        $this->assertSame($end, $booking->preferred_date_to->toDateString());
        $this->assertEquals(3870, $booking->estimated_total);
        $this->assertStringContainsString('ab 3.870', $booking->message);
    }

    public function test_submission_mails_the_guest_the_trip_confirmation_in_the_page_language(): void
    {
        Mail::fake();
        $trip = $this->makeTrip();
        $departure = now()->addDays(10)->toDateString();
        $this->addDeparture($trip, $departure, 4);

        $this->withSession(['locale' => 'de'])
            ->postJson(route('checkout.trip.store', $trip->slug), $this->payload(['departure_date' => $departure]))
            ->assertOk();

        $booking = TripBooking::query()->where('source_id', $trip->id)->latest('id')->firstOrFail();
        $this->assertSame('de', $booking->language);

        Mail::assertSent(TripCheckoutGuestMail::class, fn (TripCheckoutGuestMail $mail) => $mail->hasTo('anna@example.com')
            && $mail->locale === 'de'
            && $mail->booking->is($booking)
            && $mail->trip->is($trip));
        Mail::assertSent(TripCheckoutAdminMail::class, fn (TripCheckoutAdminMail $mail) => $mail->hasTo(config('mail.admin_email'))
            && $mail->locale === 'de'
            && $mail->booking->is($booking));
        Mail::assertNotSent(VacationBookingAdminMail::class);
        Mail::assertNotSent(VacationBookingCustomerMail::class);
    }

    public function test_guest_mail_uses_the_signed_in_users_own_language_over_the_page_language(): void
    {
        Mail::fake();
        $trip = $this->makeTrip(['year_round_availability' => true]);
        $user = User::query()->firstOrFail();
        $user->forceFill(['language' => 'en'])->save();

        $this->actingAs($user)
            ->withSession(['locale' => 'de'])
            ->postJson(route('checkout.trip.store', $trip->slug), $this->payload([
                'wish_start' => now()->addDays(30)->toDateString(),
                'wish_end' => now()->addDays(60)->toDateString(),
            ]))
            ->assertOk();

        Mail::assertSent(TripCheckoutGuestMail::class, fn (TripCheckoutGuestMail $mail) => $mail->locale === 'en');
        Mail::assertSent(TripCheckoutAdminMail::class, fn (TripCheckoutAdminMail $mail) => $mail->locale === 'en');
    }

    public function test_guest_mail_locale_falls_back_to_the_page_language(): void
    {
        $user = (new User)->forceFill(['language' => 'Deutsch']);
        $booking = new TripBooking(['language' => 'en']);

        $booking->setRelation('user', $user);
        $this->assertSame('de', $booking->customerLocale());

        $user->language = 'fr';
        $this->assertSame('en', $booking->customerLocale());

        $booking->setRelation('user', null);
        $this->assertSame('en', $booking->customerLocale());
    }

    public function test_guest_mail_renders_a_fixed_departure_in_german(): void
    {
        $trip = $this->makeTrip();
        $booking = $this->makeBooking($trip, ['preferred_date' => '2026-10-03', 'estimated_total' => 2580]);

        $mail = (new TripCheckoutGuestMail($booking, $trip, 'Eigene Ruten bringen wir mit.'))->locale('de');
        $html = $mail->render();

        $this->assertSame('Deine Anfrage für deine Angelreise ist angekommen', $mail->subject);
        $this->assertStringContainsString('Hallo Anna,', $html);
        $this->assertStringContainsString('danke für deine Anfrage zur Angelreise Test Checkout Trip. Wir prüfen die freien Plätze', $html);
        $this->assertStringContainsString('Test Checkout Trip, Sa, 03.10. – Fr, 09.10.2026: Wir prüfen die Plätze', $html);
        $this->assertStringContainsString('Pyrenäen · Spanien', $html);
        $this->assertStringContainsString('Reisezeitraum', $html);
        $this->assertStringContainsString('7 Tage · 6 Nächte', $html);
        $this->assertStringContainsString('Angelreise · 2 Personen ×', $html);
        $this->assertStringContainsString("1.290\u{00A0}€", $html);
        $this->assertStringContainsString("ca. 2.580\u{00A0}€", $html);
        $this->assertStringContainsString('„Eigene Ruten bringen wir mit.“', $html);
        $this->assertStringContainsString(__('emails.trip_checkout_guest.step_fixed_1', [], 'de'), $html);
        $this->assertStringContainsString('href="'.route('vacations.trips.show', $trip->slug).'"', $html);
    }

    public function test_guest_mail_renders_a_preferred_window_in_english(): void
    {
        $trip = $this->makeTrip(['year_round_availability' => true]);
        $booking = $this->makeBooking($trip, [
            'preferred_date' => '2027-05-01',
            'preferred_date_to' => '2027-05-31',
            'estimated_total' => 2380,
        ]);

        $mail = (new TripCheckoutGuestMail($booking, $trip))->locale('en');
        $html = $mail->render();

        $this->assertSame('Your request for your fishing trip has arrived', $mail->subject);
        $this->assertStringContainsString('Hi Anna,', $html);
        $this->assertStringContainsString(__('emails.trip_checkout_guest.preheader_window', [], 'en'), $html);
        $this->assertStringContainsString('Preferred period', $html);
        $this->assertStringContainsString('May 1 – May 31, 2027', $html);
        $this->assertStringContainsString(trans_choice('checkout.trip.days_count', 7, ['count' => 7], 'en'), $html);
        $this->assertStringNotContainsString(trans_choice('checkout.trip.nights_count', 6, ['count' => 6], 'en'), $html);
        $this->assertStringContainsString('from €1,190', $html);
        $this->assertStringContainsString('from €2,380', $html);
        $this->assertStringContainsString(__('emails.trip_checkout_guest.step_window_1', [], 'en'), $html);
        // No message section when the guest left the message empty.
        $this->assertStringNotContainsString(__('emails.trip_checkout_guest.section_message', [], 'en'), $html);
    }

    public function test_guest_mail_without_a_price_shows_on_request(): void
    {
        $trip = $this->makeTrip(['price_per_person' => null]);
        $booking = $this->makeBooking($trip, ['preferred_date' => '2026-10-03', 'estimated_total' => null]);

        $html = (new TripCheckoutGuestMail($booking, $trip))->locale('de')->render();

        $this->assertStringContainsString(__('checkout.trip.on_request', [], 'de'), $html);
        $this->assertStringNotContainsString('Angelreise · 2 Personen ×', $html);
    }

    public function test_guest_mail_is_listed_and_previewable_in_the_admin_email_templates(): void
    {
        $template = config('email_templates.templates.guest_trip_checkout_request');
        $this->assertSame('mails.guest.trip_checkout_request', $template['view']);
        $this->assertSame((new TripCheckoutGuestMail(new TripBooking, new Trip))->type, $template['log_type']);

        // Same steps as GuidingsSettingController::emailPreview().
        foreach (['de' => ['So geht es weiter', 'Pyrenäen'], 'en' => ['What happens next', 'Pyrenees']] as $locale => [$heading, $region]) {
            app()->setLocale($locale);
            $html = view($template['view'], TripCheckoutGuestMail::sample()->viewData())->render();

            $this->assertStringContainsString($heading, $html);
            $this->assertStringContainsString($region, $html);
        }
    }

    public function test_admin_mail_renders_a_fixed_departure_in_german(): void
    {
        $trip = $this->makeTrip(['title' => 'Fliegenfischen Spanien: Pyrenäen-Reise', 'group_size_max' => 8]);
        $departure = now()->addDays(10);
        $this->addDeparture($trip, $departure->toDateString(), 2);
        $booking = $this->makeBooking($trip, ['preferred_date' => $departure->toDateString(), 'estimated_total' => 2580]);

        $mail = (new TripCheckoutAdminMail($booking, $trip, 'Eigene Ruten bringen wir mit.'))->locale('de');
        $html = $mail->render();

        $this->assertSame(
            'Neue Angelreise-Anfrage · Fliegenfischen Spanien · '.$departure->format('d.m.')." · 2 Pers. · ca. 2.580\u{00A0}€",
            $mail->subject,
        );
        $this->assertTrue($mail->hasReplyTo('anna@example.com'));
        $this->assertStringContainsString('<strong style="font-weight:600;">Fliegenfischen Spanien</strong> von Anna Fischer', $html);
        $this->assertStringContainsString('beim Veranstalter', $html);
        $this->assertStringContainsString('Fliegenfischen Spanien: Pyrenäen-Reise', $html);
        $this->assertStringContainsString('Pyrenäen · Spanien', $html);
        $this->assertStringContainsString('Fester Termin', $html);
        $this->assertStringContainsString('7 Tage · 6 Nächte', $html);
        $this->assertStringContainsString('2 von 8', $html);
        $this->assertStringContainsString("Angelreise · 2 Personen × 1.290\u{00A0}€", $html);
        $this->assertStringContainsString('„Eigene Ruten bringen wir mit.“', $html);
        $this->assertStringContainsString('href="'.route('admin.trip-bookings.index').'"', $html);
    }

    public function test_admin_mail_renders_a_preferred_window_in_english(): void
    {
        $trip = $this->makeTrip(['year_round_availability' => true]);
        $booking = $this->makeBooking($trip, [
            'preferred_date' => '2027-05-01',
            'preferred_date_to' => '2027-05-31',
            'estimated_total' => 2380,
        ]);

        $mail = (new TripCheckoutAdminMail($booking, $trip))->locale('en');
        $html = $mail->render();

        $this->assertSame('New fishing trip request · Test Checkout Trip · May 1 · 2 pers. · from €2,380', $mail->subject);
        $this->assertStringContainsString('Preferred period', $html);
        $this->assertStringContainsString('May 1 – May 31, 2027', $html);
        $this->assertStringContainsString('Trip length', $html);
        $this->assertStringContainsString('Fishing trip · 2 people × from €1,190', $html);
        $this->assertStringNotContainsString('Free places at request', $html);
        $this->assertStringNotContainsString(__('emails.checkout_request_admin.section_message', [], 'en'), $html);
    }

    public function test_admin_mail_is_listed_and_previewable_in_the_admin_email_templates(): void
    {
        $template = config('email_templates.templates.admin_trip_checkout_request');
        $this->assertSame('mails.admin.checkout_request', $template['view']);
        $this->assertSame((new TripCheckoutAdminMail(new TripBooking, new Trip))->type, $template['log_type']);

        // Same steps as GuidingsSettingController::emailPreview().
        foreach (['de' => ['Neue Anfrage', '2 von 8'], 'en' => ['New request', '2 of 8']] as $locale => [$heading, $spots]) {
            app()->setLocale($locale);
            $html = view($template['view'], TripCheckoutAdminMail::sample()->viewData())->render();

            $this->assertStringContainsString($heading, $html);
            $this->assertStringContainsString($spots, $html);
        }
    }

    public function test_submission_rejects_a_full_or_unknown_departure(): void
    {
        $trip = $this->makeTrip();
        $this->addDeparture($trip, now()->addDays(10)->toDateString(), 4);
        $full = now()->addDays(20)->toDateString();
        $this->addDeparture($trip, $full, 0);

        $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload(['departure_date' => $full]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['departure_date' => __('checkout.trip.errors.date_invalid')]);

        $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['departure_date' => __('checkout.trip.errors.date_required')]);
    }

    public function test_submission_validates_the_preferred_window_party_size_and_contact(): void
    {
        $trip = $this->makeTrip(['year_round_availability' => true]);

        $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload([
            'wish_start' => now()->addDays(20)->toDateString(),
            'wish_end' => now()->addDays(10)->toDateString(),
            'persons' => 7,
            'email' => 'not-an-email',
            'phone' => '',
        ]))->assertStatus(422)->assertJsonValidationErrors([
            'wish_end' => __('checkout.trip.errors.wish_order'),
            'persons',
            'email' => __('checkout.tour.errors.email_invalid'),
            'phone' => __('checkout.tour.errors.phone_required'),
        ]);

        $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload([
            'wish_start' => now()->subDay()->toDateString(),
            'wish_end' => now()->addDays(10)->toDateString(),
        ]))->assertStatus(422)->assertJsonValidationErrors(['wish_start' => __('checkout.trip.errors.wish_invalid')]);
    }

    public function test_submission_for_a_draft_trip_is_refused(): void
    {
        $trip = $this->makeTrip(['status' => 'draft', 'year_round_availability' => true]);

        $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload([
            'wish_start' => now()->addDays(10)->toDateString(),
            'wish_end' => now()->addDays(20)->toDateString(),
        ]))->assertStatus(410)->assertJson(['success' => false]);

        $this->assertSame(0, TripBooking::query()->where('source_id', $trip->id)->count());
    }

    public function test_thank_you_page_is_only_visible_to_the_session_that_sent_the_request(): void
    {
        $trip = $this->makeTrip();
        $departure = now()->addDays(10)->toDateString();
        $this->addDeparture($trip, $departure, 4);

        $this->postJson(route('checkout.trip.store', $trip->slug), $this->payload(['departure_date' => $departure]))->assertOk();

        $booking = TripBooking::query()->where('source_id', $trip->id)->latest('id')->firstOrFail();
        $url = route('checkout.trip.thank-you', [$trip->slug, $booking->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee(__('checkout.trip.success_title'))
            ->assertSee('anna@example.com')
            ->assertSee('ca. 2.580', false);

        $otherTrip = $this->makeTrip();
        $this->get(route('checkout.trip.thank-you', [$otherTrip->slug, $booking->id]))->assertNotFound();

        // A fresh session (someone guessing ids) gets a 404.
        $this->flushSession();
        $this->get($url)->assertNotFound();
    }

    public function test_trip_page_booking_ctas_lead_to_the_checkout(): void
    {
        $trip = $this->makeTrip();
        $this->addDeparture($trip, now()->addDays(10)->toDateString(), 4);

        $html = $this->get(route('vacations.trips.show', $trip->slug))->assertOk()->getContent();

        // @json escapes slashes.
        $this->assertStringContainsString(str_replace('/', '\/', route('checkout.trip.show', $trip->slug)), $html);
        $this->assertStringNotContainsString('id="tripContactModal"', $html);
        $this->assertStringContainsString('id="tripGeneralContactModal"', $html);
    }

    public function test_checkout_lives_under_the_checkout_prefix_and_old_urls_redirect(): void
    {
        $trip = $this->makeTrip();
        $date = now()->addDays(10)->toDateString();
        $this->addDeparture($trip, $date, 4);

        $this->assertSame('/checkout/trips/'.$trip->slug, route('checkout.trip.show', $trip->slug, false));
        $this->assertSame('/checkout/trips/'.$trip->slug.'/thank-you/5', route('checkout.trip.thank-you', [$trip->slug, 5], false));

        $this->get('/vacations/trips/'.$trip->slug.'/checkout?date='.$date.'&persons=3&utm_source=x')
            ->assertStatus(301)
            ->assertRedirect(route('checkout.trip.show', ['slug' => $trip->slug, 'date' => $date, 'persons' => 3]));
    }

    private function makeBooking(Trip $trip, array $overrides = []): TripBooking
    {
        return TripBooking::query()->create(array_merge([
            'source_type' => TripBooking::SOURCE_TRIP,
            'source_id' => $trip->id,
            'number_of_persons' => 2,
            'currency' => 'EUR',
            'name' => 'Anna Fischer',
            'first_name' => 'Anna',
            'last_name' => 'Fischer',
            'email' => 'anna@example.com',
            'phone_country_code' => '+49',
            'phone' => '151 2345678',
            'message' => 'Summary',
            'language' => 'de',
            'status' => TripBooking::STATUS_OPEN,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
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
        $this->assertMatchesRegularExpression('/<script type="application\/json" id="trip-checkout-config">(.*?)<\/script>/s', $html);
        preg_match('/<script type="application\/json" id="trip-checkout-config">(.*?)<\/script>/s', $html, $match);

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    private function addDeparture(Trip $trip, string $date, ?int $spots): void
    {
        $trip->availabilityDates()->create(['departure_date' => $date, 'spots_available' => $spots]);
    }

    private function makeTrip(array $overrides = []): Trip
    {
        $user = User::query()->first() ?? User::factory()->create();

        return Trip::query()->create(array_merge([
            'title' => 'Test Checkout Trip',
            'slug' => 'test-checkout-trip-'.uniqid(),
            'description' => 'Fly fishing for trout.',
            'location' => 'Test Location',
            'region' => 'Pyrenäen',
            'country' => 'Spanien',
            'status' => 'active',
            'user_id' => $user->id,
            'duration_days' => 7,
            'duration_nights' => 6,
            'group_size_min' => 2,
            'group_size_max' => 6,
            'price_per_person' => 1290,
            'currency' => 'EUR',
        ], $overrides));
    }
}
