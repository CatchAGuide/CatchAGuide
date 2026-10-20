<?php

namespace Tests\Feature\Guidings;

use App\Enums\GuideStatus;
use App\Enums\VoucherDesign;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuideVoucherSettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        ]);
    }

    private function guide(): User
    {
        return User::factory()->create([
            'firstname' => 'Thomas',
            'lastname' => 'Berger',
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ])->fresh();
    }

    private function tour(User $guide, array $overrides = []): Guiding
    {
        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Settings Tour '.uniqid(),
            'slug' => 'settings-tour-'.uniqid(),
            'location' => 'Speyer',
            'status' => 1,
            'max_guests' => 2,
            'duration' => 4,
            'duration_type' => 'hours',
            'price' => 0,
            'price_type' => 'per_person',
            'prices' => json_encode([['person' => 1, 'amount' => 100], ['person' => 2, 'amount' => 200]]),
            'pricing_extra' => json_encode([]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ], $overrides))->save();

        return $guiding->fresh();
    }

    public function test_new_tours_and_guides_default_to_vouchers_on_with_the_standard_design(): void
    {
        $guide = $this->guide();

        $this->assertTrue($this->tour($guide)->acceptsVouchers());
        $this->assertSame(VoucherDesign::CagStandard, $guide->voucherDesign());
    }

    public function test_guide_sees_their_tours_and_design_options(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $draft = $this->tour($guide, ['status' => 2]);
        $other = $this->tour($this->guide());

        $this->actingAs($guide)
            ->get(route('profile.vouchers'))
            ->assertOk()
            ->assertSee(__('profile.vouchers.design_title'))
            ->assertSee('id="voucher_enabled_'.$tour->id.'"', false)
            ->assertSee('id="voucher_enabled_'.$draft->id.'"', false)
            ->assertSee(__('profile.vouchers.tour_inactive'))
            ->assertSeeInOrder(['id="voucher_enabled_'.$tour->id.'"', __('profile.vouchers.unpublished_group', ['count' => 1]), 'id="voucher_enabled_'.$draft->id.'"'], false)
            ->assertDontSee('id="voucher_enabled_'.$other->id.'"', false)
            ->assertSee(route('profile.vouchers.sample'), false);
    }

    public function test_non_guides_are_sent_to_the_guide_application(): void
    {
        $user = User::factory()->create(['is_guide' => 0, 'guide_status' => null])->fresh();
        $this->assertFalse($user->canViewGuideTools());

        $this->actingAs($user)->get(route('profile.vouchers'))->assertRedirect(route('profile.becomeguide'));
        $this->actingAs($user)->put(route('profile.vouchers.update'), ['voucher_design' => 'guide_own'])->assertForbidden();
        $this->actingAs($user)->get(route('profile.vouchers.sample'))->assertForbidden();
    }

    public function test_update_saves_the_design_and_only_touches_the_guides_own_tours(): void
    {
        $guide = $this->guide();
        $keep = $this->tour($guide);
        $switchOff = $this->tour($guide);
        $switchOn = $this->tour($guide, ['voucher_enabled' => false]);
        $foreign = $this->tour($this->guide());

        $this->actingAs($guide)
            ->put(route('profile.vouchers.update'), [
                'voucher_design' => VoucherDesign::GuideOwn->value,
                'voucher_enabled' => [$keep->id, $switchOn->id, $foreign->id],
            ])
            ->assertRedirect(route('profile.vouchers'))
            ->assertSessionHas('message', __('profile.vouchers.saved'));

        $this->assertSame(VoucherDesign::GuideOwn, $guide->fresh()->voucherDesign());
        $this->assertTrue($keep->fresh()->acceptsVouchers());
        $this->assertFalse($switchOff->fresh()->acceptsVouchers());
        $this->assertTrue($switchOn->fresh()->acceptsVouchers());
        $this->assertTrue($foreign->fresh()->acceptsVouchers());
    }

    public function test_unticking_every_tour_switches_vouchers_off_everywhere(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);

        $this->actingAs($guide)
            ->put(route('profile.vouchers.update'), ['voucher_design' => VoucherDesign::CagStandard->value])
            ->assertRedirect(route('profile.vouchers'));

        $this->assertFalse($tour->fresh()->acceptsVouchers());
    }

    public function test_update_rejects_an_unknown_design(): void
    {
        $guide = $this->guide();

        $this->actingAs($guide)
            ->put(route('profile.vouchers.update'), ['voucher_design' => 'gold'])
            ->assertSessionHasErrors('voucher_design');

        $this->assertSame(VoucherDesign::CagStandard, $guide->fresh()->voucherDesign());
    }

    public function test_sample_voucher_is_a_pdf_with_the_guides_name(): void
    {
        $guide = $this->guide();

        $response = $this->actingAs($guide)->get(route('profile.vouchers.sample'));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_create_tour_form_offers_vouchers_switched_on_by_default(): void
    {
        $this->actingAs($this->guide())
            ->get(route('profile.newguiding'))
            ->assertOk()
            ->assertSee(__('newguidings.voucher_enabled'))
            ->assertSee('id="voucher_enabled_yes" checked', false)
            ->assertDontSee('id="voucher_enabled_no" checked', false);
    }

    public function test_edit_tour_form_shows_the_tours_voucher_setting(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide, ['voucher_enabled' => false]);

        $this->actingAs($guide)
            ->get(route('guidings.edit_newguiding', $tour))
            ->assertOk()
            ->assertSee('id="voucher_enabled_no" checked', false)
            ->assertDontSee('id="voucher_enabled_yes" checked', false);
    }

    public function test_saving_the_tour_form_updates_the_voucher_setting(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);

        $this->actingAs($guide)
            ->postJson(route('profile.newguiding.save-draft-sync'), $this->draftPayload($tour, ['voucher_enabled' => '0']))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertFalse($tour->fresh()->acceptsVouchers());

        $this->actingAs($guide)
            ->postJson(route('profile.newguiding.save-draft-sync'), $this->draftPayload($tour, ['voucher_enabled' => '1']))
            ->assertOk();

        $this->assertTrue($tour->fresh()->acceptsVouchers());
    }

    public function test_saving_the_tour_form_without_the_field_keeps_the_voucher_setting(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide, ['voucher_enabled' => false]);

        $this->actingAs($guide)
            ->postJson(route('profile.newguiding.save-draft-sync'), $this->draftPayload($tour))
            ->assertOk();

        $this->assertFalse($tour->fresh()->acceptsVouchers());
    }

    public function test_saving_the_tour_form_rejects_an_invalid_voucher_value(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);

        $this->actingAs($guide)
            ->postJson(route('profile.newguiding.save-draft-sync'), $this->draftPayload($tour, ['voucher_enabled' => 'maybe']))
            ->assertJsonValidationErrors('voucher_enabled');

        $this->assertTrue($tour->fresh()->acceptsVouchers());
    }

    private function draftPayload(Guiding $tour, array $overrides = []): array
    {
        return array_merge([
            'is_draft' => 1,
            'is_update' => 1,
            'guiding_id' => $tour->id,
            'title' => $tour->title,
            'location' => $tour->location,
            'latitude' => '49.3172',
            'longitude' => '8.4412',
        ], $overrides);
    }
}
