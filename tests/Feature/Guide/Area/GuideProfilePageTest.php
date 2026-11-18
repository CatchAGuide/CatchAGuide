<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\GuideStatus;
use App\Enums\GuideType;
use App\Http\Livewire\Guide\Profile;
use App\Models\GuideTag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class GuideProfilePageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');
    }

    private function guide(bool $beta = true, string $type = GuideType::PRIVATE): User
    {
        $guide = User::factory()->create([
            'firstname' => 'Lars',
            'lastname' => 'Petersen',
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'guide_type' => $type,
            'language' => 'en',
        ]);
        $guide->forceFill(['guide_area_beta' => $beta, 'banktransferdetails' => 'Sparkasse DE89 3704 0044 0532 0130 00'])->save();
        $guide->information->update([
            'languages' => 'Deutsch, Englisch',
            'about_me' => 'I have been fishing the Elbe since I was a kid.',
            'favorite_fish' => 'Zander',
            'fishing_start_year' => 1990,
        ]);

        return $guide->fresh(['information']);
    }

    public function test_guides_without_the_beta_flag_go_to_the_old_page(): void
    {
        $this->actingAs($this->guide(beta: false))
            ->get(route('guide.area.profile'))
            ->assertRedirect(route('profile.guide-profile'));
    }

    public function test_kill_switch_hides_the_page_for_flagged_guides(): void
    {
        config(['guide_area.enabled' => false]);

        $this->actingAs($this->guide())
            ->get(route('guide.area.profile'))
            ->assertRedirect(route('profile.guide-profile'));
    }

    public function test_non_guides_cannot_open_the_page(): void
    {
        $user = User::factory()->create(['is_guide' => 0, 'guide_status' => null]);
        $user->forceFill(['guide_area_beta' => true])->save();

        $this->actingAs($user)->get(route('guide.area.profile'))->assertRedirect(route('profile.becomeguide'));
    }

    public function test_guide_area_pages_keep_the_site_header_and_footer(): void
    {
        $guide = $this->guide();

        foreach (['guide.area.overview', 'guide.area.bookings', 'guide.area.todos', 'guide.area.calendar', 'guide.area.profile', 'guide.area.tours', 'guide.area.vouchers.settings', 'guide.area.reviews', 'guide.area.invoices'] as $route) {
            $html = $this->actingAs($guide)->get(route($route))->assertOk()->getContent();

            $this->assertStringContainsString('data-site-page-header', $html, $route);
            $this->assertStringContainsString('<footer class="site-footer', $html, $route);
            $this->assertStringContainsString('class="ga-shell"', $html, $route);
            $this->assertSame(1, substr_count($html, '<title>'), $route);
            $this->assertStringContainsString('NOINDEX,NOFOLLOW', $html, $route);
            // No catalog search band in the Guide Area.
            $this->assertStringNotContainsString('data-site-page-header-search', $html, $route);
        }
    }

    public function test_flagged_guide_sees_the_prefilled_page(): void
    {
        $this->actingAs($this->guide())
            ->get(route('guide.area.profile'))
            ->assertOk()
            ->assertSee(__('guide_area.profile.title'))
            ->assertSee('NOINDEX', false);

        Livewire::actingAs($this->guide())
            ->test(Profile::class)
            ->assertSet('displayName', 'Lars')
            ->assertSet('languages', ['de', 'en'])
            ->assertSet('fishingSince', '1990')
            // IBAN pulled out of the old free-text field as a starting point.
            ->assertSet('bankIban', 'DE89 3704 0044 0532 0130 00')
            ->assertSet('legacyBankDetails', 'Sparkasse DE89 3704 0044 0532 0130 00');
    }

    public function test_saving_writes_profile_tags_and_payment_details(): void
    {
        $guide = $this->guide();

        Livewire::actingAs($guide)
            ->test(Profile::class)
            ->set('displayName', 'Lars the Zander Guide')
            ->set('languages', ['de', 'en', 'Plattdeutsch'])
            ->set('aboutMe', 'Zander, pike and perch on the Elbe – relaxed days and honest tips.')
            ->set('specialties', ['spin_fishing', 'Vertikalangeln', 'Faulenzer'])
            ->set('goodFor', ['kids_welcome'])
            ->set('barAllowed', true)
            ->set('bankAllowed', true)
            ->set('bankHolder', 'Lars Petersen')
            ->set('bankIban', 'de89370400440532013000')
            ->set('bankBic', 'cobadeffxxx')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('guide-area-toast')
            ->assertSet('bankIban', 'DE89 3704 0044 0532 0130 00');

        $guide->refresh();
        $info = $guide->information;
        $this->assertSame('Lars the Zander Guide', $info->display_name);
        $this->assertSame('German, English, Plattdeutsch', $info->languages);
        $this->assertTrue((bool) $guide->bar_allowed);
        $this->assertTrue((bool) $guide->banktransfer_allowed);
        $this->assertSame('DE89370400440532013000', $guide->banktransfer_iban);
        $this->assertSame('COBADEFFXXX', $guide->banktransfer_bic);
        // Free text kept in sync for the guest confirmation mail and the old page.
        $this->assertSame('Lars Petersen · IBAN DE89 3704 0044 0532 0130 00 · BIC COBADEFFXXX', $guide->banktransferdetails);
        // Stored encrypted, not in plain text.
        $raw = DB::table('users')->where('id', $guide->id)->value('banktransfer_iban');
        $this->assertNotSame('DE89370400440532013000', $raw);

        $tags = GuideTag::where('user_id', $guide->id)->orderBy('kind')->orderBy('position')->get();
        $this->assertSame(
            [['good_for', 'kids_welcome', false], ['specialty', 'spin_fishing', false], ['specialty', 'vertical_jigging', false], ['specialty', 'Faulenzer', true]],
            $tags->map(fn ($t) => [$t->kind, $t->value, $t->is_custom])->all()
        );
    }

    public function test_validation_rejects_bad_iban_and_no_payment_method(): void
    {
        Livewire::actingAs($this->guide())
            ->test(Profile::class)
            ->set('bankAllowed', true)
            ->set('bankIban', 'DE00 1234')
            ->call('save')
            ->assertHasErrors(['bankIban']);

        Livewire::actingAs($this->guide())
            ->test(Profile::class)
            ->set('barAllowed', false)
            ->set('bankAllowed', false)
            ->set('paypalAllowed', false)
            ->call('save')
            ->assertHasErrors(['paymentMethods']);
    }

    public function test_private_guides_cannot_set_company_fields(): void
    {
        $guide = $this->guide();

        Livewire::actingAs($guide)
            ->test(Profile::class)
            ->set('barAllowed', true)
            ->set('crewSize', '5')
            ->call('save')
            ->assertHasErrors(['crewSize']);

        $company = $this->guide(type: GuideType::COMPANY);
        Livewire::actingAs($company)
            ->test(Profile::class)
            ->set('barAllowed', true)
            ->set('crewSize', '5')
            ->set('foundedYear', '2008')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(5, (int) $company->fresh()->information->crew_size);
        $this->assertSame(2008, (int) $company->fresh()->information->founded_year);
    }

    public function test_long_existing_about_me_can_still_be_saved(): void
    {
        $guide = $this->guide();
        $long = str_repeat('Zander am Rhein. ', 100); // ~1,700 chars, over the new 1,000 limit
        $guide->information->update(['about_me' => $long]);

        Livewire::actingAs($guide->fresh())
            ->test(Profile::class)
            ->set('barAllowed', true)
            ->call('save')
            ->assertHasNoErrors();
    }
}
