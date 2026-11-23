<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\AccountVerificationStatus;
use App\Enums\GuideStatus;
use App\Enums\GuideType;
use App\Http\Livewire\Guide\Account;
use App\Mail\Account\PasswordChangedMail;
use App\Mail\Guide\GuideAccountVerificationMail;
use App\Mail\Guide\GuideBillingChangedMail;
use App\Mail\Guide\GuideBillingReviewAdminMail;
use App\Models\AdminNotification;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserInformation;
use App\Services\Guide\Area\GuideAccountService;
use App\Services\Guide\Area\GuideOptimizationService;
use App\Services\Guide\GuideStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class GuideAccountTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true, 'guide_area.customer_enabled' => true]);
        URL::forceRootUrl('http://localhost');
        Mail::fake();
    }

    private function guide(array $information = [], array $attributes = []): User
    {
        $info = UserInformation::create($information + [
            'birthday' => '1984-06-12',
            'address' => 'Elbchaussee',
            'address_number' => '112',
            'postal' => '22605',
            'city' => 'Hamburg',
            'country' => 'Deutschland',
        ]);

        $guide = User::factory()->create($attributes + [
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'guide_type' => GuideType::PRIVATE,
            'language' => 'en',
            'firstname' => 'Lars',
            'lastname' => 'Petersen',
            'phone' => '151 2345 6789',
            'phone_country_code' => '+49',
            'user_information_id' => $info->id,
        ]);
        $guide->forceFill(['account_verification_status' => AccountVerificationStatus::Verified, 'account_verified_at' => now()])->save();

        return $guide->fresh(['information']);
    }

    private function page(User $guide)
    {
        return Livewire::actingAs($guide)->test(Account::class);
    }

    public function test_guides_get_account_and_security_with_billing_on_the_settings_and_password_routes(): void
    {
        $guide = $this->guide();

        $this->actingAs($guide)->get(route('profile.settings'))
            ->assertOk()
            ->assertSee(__('guide_area.account.billing'))
            ->assertSee(__('guide_area.account.status.verified'))
            ->assertSee('Elbchaussee');

        $this->actingAs($guide)->get(route('profile.password'))
            ->assertOk()
            ->assertSee(__('guide_area.account.password_submit'));
    }

    public function test_customers_keep_the_customer_account_page(): void
    {
        $customer = User::factory()->create(['is_guide' => null, 'guide_status' => null]);

        $this->actingAs($customer)->get(route('profile.settings'))
            ->assertOk()
            ->assertSee(__('guide_area.customer.account.tabs.details'))
            ->assertDontSee(__('guide_area.account.billing'));
    }

    public function test_old_free_text_country_is_shown_as_its_code(): void
    {
        $this->page($this->guide())->assertSet('billing.country', 'DE');
    }

    public function test_personal_details_save_without_a_new_verification(): void
    {
        $guide = $this->guide();

        $this->page($guide)
            ->set('personal.firstname', 'Lasse')
            ->set('personal.language', 'de')
            ->call('savePersonal')
            ->assertHasNoErrors();

        $guide->refresh();
        $this->assertSame('Lasse', $guide->firstname);
        $this->assertSame('de', $guide->language);
        $this->assertSame(AccountVerificationStatus::Verified, $guide->account_verification_status);
        Mail::assertNothingQueued();
    }

    public function test_changing_only_the_phone_keeps_the_verification(): void
    {
        $guide = $this->guide();

        $this->page($guide)
            ->set('billing.phone', '170 000 000')
            ->call('saveBilling')
            ->assertHasNoErrors();

        $guide->refresh();
        $this->assertSame('170 000 000', $guide->phone);
        $this->assertSame('DE', $guide->information->country);
        $this->assertSame(AccountVerificationStatus::Verified, $guide->account_verification_status);
        Mail::assertNotQueued(GuideBillingChangedMail::class);
    }

    public function test_switching_to_company_resets_verification_notifies_and_keeps_the_birthday(): void
    {
        $guide = $this->guide();

        $this->page($guide)
            ->set('billing.account_type', GuideType::COMPANY)
            ->assertSee(__('guide_area.account.type_change_warning'))
            ->set('billing.company_name', 'Elbe Guiding')
            ->set('legalFormChoice', 'DE|GmbH')
            ->set('billing.vat_id', 'de 123 456 789')
            ->call('saveBilling')
            ->assertHasNoErrors();

        $guide->refresh();
        $this->assertSame(GuideType::COMPANY, $guide->guide_type);
        $this->assertSame('Elbe Guiding', $guide->information->company_name);
        $this->assertSame('GmbH', $guide->information->legal_form);
        $this->assertSame('DE123456789', $guide->tax_id);
        $this->assertSame('1984-06-12', $guide->information->birthday->toDateString(), 'the individual data is kept for 30 days');
        $this->assertSame(AccountVerificationStatus::Pending, $guide->account_verification_status);
        $this->assertNotNull($guide->account_type_changed_at);
        $this->assertSame(GuideStatus::VERIFIED, $guide->guide_status, 'tours stay online');

        Mail::assertQueued(GuideBillingChangedMail::class, fn ($mail) => $mail->hasTo($guide->email) && $mail->typeChanged);
        Mail::assertQueued(GuideBillingReviewAdminMail::class);
        $this->assertTrue(AdminNotification::where('type', 'guide_billing_review')->where('meta->user_id', $guide->id)->exists());
    }

    public function test_a_second_change_while_in_review_does_not_ping_ops_again(): void
    {
        $guide = $this->guide();
        $guide->forceFill(['account_verification_status' => AccountVerificationStatus::Pending])->save();

        $this->page($guide->fresh())
            ->set('billing.city', 'Lübeck')
            ->call('saveBilling')
            ->assertHasNoErrors();

        Mail::assertQueued(GuideBillingChangedMail::class);
        Mail::assertNotQueued(GuideBillingReviewAdminMail::class);
    }

    public function test_company_needs_name_and_legal_form_and_other_takes_free_text(): void
    {
        $guide = $this->guide();

        $this->page($guide)
            ->set('billing.account_type', GuideType::COMPANY)
            ->call('saveBilling')
            ->assertHasErrors(['billing.company_name', 'billing.legal_form'])
            ->set('billing.company_name', 'Fjord AS')
            ->set('legalFormChoice', Account::LEGAL_FORM_OTHER)
            ->set('legalFormOther', 'Stiftung')
            ->call('saveBilling')
            ->assertHasNoErrors();

        $this->assertSame('Stiftung', $guide->fresh()->information->legal_form);
    }

    public function test_individuals_must_be_adults(): void
    {
        $this->page($this->guide())
            ->set('billing.birthday', now()->subYears(17)->toDateString())
            ->call('saveBilling')
            ->assertHasErrors(['billing.birthday' => 'before_or_equal']);
    }

    public function test_vat_id_format_is_checked_per_country(): void
    {
        $this->page($this->guide())
            ->set('billing.vat_id', 'DE12345')
            ->call('saveBilling')
            ->assertHasErrors(['billing.vat_id'])
            ->set('billing.vat_id', '123456789')
            ->call('saveBilling')
            ->assertHasErrors(['billing.vat_id']);
    }

    public function test_not_vat_registered_clears_the_vat_id_and_passes_the_vat_todo(): void
    {
        $guide = $this->guide([], ['tax_id' => null]);

        $this->page($guide)
            ->set('billing.vat_id', 'DE123456789')
            ->set('billing.vat_not_registered', true)
            ->assertSet('billing.vat_id', '')
            ->call('saveBilling')
            ->assertHasNoErrors();

        $guide->refresh();
        $this->assertTrue($guide->information->vat_not_registered);
        $this->assertNull($guide->tax_id);

        $checks = collect(app(GuideOptimizationService::class)->evaluate($guide, true)['sections'])
            ->flatMap(fn ($section) => $section['checks'] ?? [])
            ->keyBy('key');
        $this->assertTrue($checks['vat']['passed']);
    }

    public function test_password_change_stamps_the_date_and_sends_the_security_notice(): void
    {
        $guide = $this->guide([], ['password' => Hash::make('old-password-1')]);

        $this->page($guide)
            ->set('passwordOpen', true)
            ->set('currentPassword', 'old-password-1')
            ->set('newPassword', 'new-password-2')
            ->set('newPasswordConfirmation', 'new-password-2')
            ->call('changePassword')
            ->assertHasNoErrors()
            ->assertSet('passwordOpen', false);

        $guide->refresh();
        $this->assertTrue(Hash::check('new-password-2', $guide->password));
        $this->assertNotNull($guide->password_changed_at);
        Mail::assertQueued(PasswordChangedMail::class, fn ($mail) => $mail->hasTo($guide->email));
    }

    public function test_admin_approves_and_rejects_with_a_reason(): void
    {
        $guide = $this->guide();
        $guide->forceFill(['account_verification_status' => AccountVerificationStatus::Pending])->save();
        $employee = Employee::factory()->create();

        $this->actingAs($employee, 'employees')->get(route('admin.guide-billing.index'))
            ->assertOk()
            ->assertSee($guide->email);

        $this->actingAs($employee, 'employees')
            ->post(route('admin.guide-billing.reject', $guide), ['rejection_reason' => 'Company name does not match the register.'])
            ->assertRedirect();
        $guide->refresh();
        $this->assertSame(AccountVerificationStatus::Rejected, $guide->account_verification_status);
        $this->assertSame('Company name does not match the register.', $guide->account_rejection_reason);

        $this->page($guide)->assertSee('Company name does not match the register.');

        $this->actingAs($employee, 'employees')->post(route('admin.guide-billing.approve', $guide))->assertRedirect();
        $guide->refresh();
        $this->assertSame(AccountVerificationStatus::Verified, $guide->account_verification_status);
        $this->assertSame($employee->id, (int) $guide->account_verified_by);
        Mail::assertQueued(GuideAccountVerificationMail::class, 2);
    }

    public function test_other_type_data_is_cleared_after_thirty_days(): void
    {
        $guide = $this->guide(['company_name' => 'Old GmbH', 'legal_form' => 'GmbH']);
        $guide->forceFill(['account_type_changed_at' => now()->subDays(31)])->save();
        $recent = $this->guide(['company_name' => 'Keep GmbH', 'legal_form' => 'GmbH']);
        $recent->forceFill(['account_type_changed_at' => now()->subDays(5)])->save();

        $this->artisan('guide-area:purge-account-type-data')->assertSuccessful();

        $guide->refresh();
        $this->assertNull($guide->information->company_name);
        $this->assertNull($guide->information->legal_form);
        $this->assertNotNull($guide->information->birthday, 'the current type keeps its data');
        $this->assertNull($guide->account_type_changed_at);
        $this->assertSame('Keep GmbH', $recent->fresh()->information->company_name);
    }

    public function test_approving_a_guide_application_verifies_the_billing_data(): void
    {
        $applicant = User::factory()->create(['guide_status' => null, 'is_guide' => 0]);
        $service = app(GuideStatusService::class);

        $service->markPending($applicant);
        $this->assertSame(AccountVerificationStatus::Pending, $applicant->fresh()->account_verification_status);

        $service->markVerified($applicant->fresh());
        $this->assertSame(AccountVerificationStatus::Verified, $applicant->fresh()->account_verification_status);
    }

    public function test_all_account_emails_render_in_both_languages(): void
    {
        $guide = $this->guide(['company_name' => 'Elbe Guiding', 'legal_form' => 'GmbH'], ['guide_type' => GuideType::COMPANY, 'tax_id' => 'DE123456789']);
        $guide->forceFill(['account_verification_status' => AccountVerificationStatus::Rejected, 'account_rejection_reason' => 'Register mismatch', 'password_changed_at' => now()])->save();

        foreach (['en', 'de'] as $locale) {
            app()->setLocale($locale);
            $this->assertStringContainsString(__('guide_area.account.mail.not_you_title'), (new GuideBillingChangedMail($guide, true))->render());
            $this->assertStringContainsString('Elbe Guiding', (new GuideBillingReviewAdminMail($guide, true))->render());
            $this->assertStringContainsString('Register mismatch', (new GuideAccountVerificationMail($guide))->render());
            $this->assertStringContainsString(__('guide_area.account.mail.reset_password'), (new PasswordChangedMail($guide))->render());
        }
    }

    public function test_api_reads_and_updates_the_account(): void
    {
        $guide = $this->guide();
        Sanctum::actingAs($guide);

        $this->getJson(route('api.guide.account.show'))
            ->assertOk()
            ->assertJsonPath('data.billing.country', 'DE')
            ->assertJsonPath('data.verification.status', 'verified');

        $this->putJson(route('api.guide.account.personal'), ['firstname' => 'Lars', 'lastname' => 'P.', 'language' => 'en'])
            ->assertOk()
            ->assertJsonPath('data.personal.lastname', 'P.');

        $billing = app(GuideAccountService::class)->billing($guide->fresh());
        $this->putJson(route('api.guide.account.billing'), ['postal' => '22609'] + $billing)
            ->assertOk()
            ->assertJsonPath('meta.review_requested', true)
            ->assertJsonPath('data.verification.status', 'pending');

        $this->putJson(route('api.guide.account.billing'), ['vat_id' => 'XX'] + $billing)
            ->assertStatus(422)
            ->assertJsonValidationErrors('vat_id');
    }
}
