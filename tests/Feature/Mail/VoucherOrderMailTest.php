<?php

namespace Tests\Feature\Mail;

use App\Enums\VoucherDesign;
use App\Mail\Guest\VoucherOrderGuestMail;
use App\Mail\Guide\VoucherOrderGuideMail;
use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VoucherOrderMailTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['vouchers.pdf_disk' => 'local', 'mail.admin_email' => 'ops@example.com']);
        Storage::fake('local');
    }

    public static function locales(): array
    {
        return [['en'], ['de']];
    }

    /**
     * @dataProvider locales
     */
    public function test_guest_mail_says_payment_goes_to_the_guide_and_attaches_nothing(string $locale): void
    {
        app()->setLocale($locale);
        $mail = VoucherOrderGuestMail::sample();

        $html = $mail->render();

        $this->assertStringContainsString(e(__('emails.voucher_order_guest.payment_title')), $html);
        $this->assertStringContainsString(e(__('emails.voucher_order_guest.disclaimer')), $html);
        $this->assertStringContainsString('CAG-V-7KQ3PX', $html);
        $this->assertStringContainsString(e(__('emails.voucher_order_guest.payment_text', ['guide' => 'Thomas'])), $html);
        $this->assertStringNotContainsString('Berger Angelguiding', $html, 'The guest sees only the guide first name.');
        // The personal message must not collide with the $message every mail view receives.
        $this->assertStringContainsString(e($mail->order->personal_message), $html);
        $this->assertStringNotContainsString('emails.voucher_order_guest.', $html, 'No untranslated keys.');
        $this->assertSame([], $mail->rawAttachments);
    }

    /**
     * @dataProvider locales
     */
    public function test_guest_mail_shows_next_steps_first_and_the_guides_contact_details(string $locale): void
    {
        app()->setLocale($locale);

        $html = VoucherOrderGuestMail::sample()->render();

        $this->assertLessThan(
            strpos($html, e(__('emails.voucher_order_guest.section_order'))),
            strpos($html, e(__('emails.voucher_order_guest.next_title'))),
            'The next steps come before the order details.',
        );
        $this->assertMatchesRegularExpression('/>\s*Thomas\s*</', $html, 'The guide row shows the first name only.');
        $this->assertStringNotContainsString('Berger</td>', $html);
        $this->assertStringContainsString(e(__('emails.voucher_order_guest.guide_phone')), $html);
        $this->assertStringContainsString('+49 170 1234567', $html);
        $this->assertStringContainsString(e(__('emails.voucher_order_guest.guide_email')), $html);
        $this->assertStringContainsString('thomas.berger@example.com', $html);
        $this->assertStringNotContainsString('Köln', $html, 'The location row is gone.');
    }

    /**
     * @dataProvider locales
     */
    public function test_guide_mail_preview_renders_both_designs(string $locale): void
    {
        app()->setLocale($locale);

        $standard = VoucherOrderGuideMail::sample(VoucherDesign::CagStandard)->render();
        $own = VoucherOrderGuideMail::sample(VoucherDesign::GuideOwn)->render();

        foreach ([$standard, $own] as $html) {
            $this->assertStringContainsString(e(__('emails.voucher_order_guide.payment_title')), $html);
            $this->assertStringContainsString('lena.schmidt@example.com', $html);
            $this->assertStringContainsString($locale === 'de' ? 'Petri Heil!' : 'Tight lines!', $html);
            $this->assertStringNotContainsString('emails.voucher_order_guide.', $html, 'No untranslated keys.');
        }

        $this->assertStringContainsString(e(__('emails.voucher_order_guide.step_3_pdf', ['buyer' => 'Lena Schmidt'])), $standard);
        $this->assertStringContainsString(e(__('emails.voucher_order_guide.step_3_own', ['buyer' => 'Lena Schmidt'])), $own);
    }

    public function test_guide_order_mail_attaches_the_stored_voucher_pdf_for_the_standard_design(): void
    {
        $order = $this->order(VoucherDesign::CagStandard, 'vouchers/CAG-V-TEST22.pdf');
        Storage::disk('local')->put($order->pdf_path, '%PDF-1.7 test');

        $mail = (new VoucherOrderGuideMail($order))->locale('en');
        $html = $mail->render();

        $mail->assertHasAttachedData('%PDF-1.7 test', 'Gift-voucher-CAG-V-TEST22.pdf', ['mime' => 'application/pdf']);
        $this->assertStringContainsString(e(__('emails.voucher_order_guide.payment_text_pdf', [], 'en')), $html);
        $this->assertStringContainsString(e(route('profile.bookings')), $html);
        $this->assertStringNotContainsString('voucher-payment', $html, 'The guide no longer confirms payments.');
        $mail->assertHasReplyTo('buyer@example.com');
        $mail->assertHasSubject(__('emails.voucher_order_guide.subject', ['tour' => 'Snapshot Tour'], 'en'));
    }

    public function test_guide_order_mail_for_the_own_design_attaches_nothing(): void
    {
        $order = $this->order(VoucherDesign::GuideOwn, null);

        $mail = (new VoucherOrderGuideMail($order))->locale('en');
        $html = $mail->render();

        $this->assertSame([], $mail->rawAttachments);
        $this->assertStringContainsString(e(__('emails.voucher_order_guide.payment_text_own', [], 'en')), $html);
        $this->assertStringContainsString(e(__('emails.voucher_order_guide.step_3_own', ['buyer' => 'Bea Buyer'], 'en')), $html);
    }

    public function test_guide_mail_is_logged_in_the_voucher_language_and_the_guest_mail_in_the_order_language(): void
    {
        $order = $this->order(VoucherDesign::GuideOwn, null);
        $order->voucher_language = 'de';

        $this->assertSame('de', (new VoucherOrderGuideMail($order))->language);
        $this->assertSame('en', (new VoucherOrderGuestMail($order))->language);
    }

    private function order(VoucherDesign $design, ?string $pdfPath): VoucherOrder
    {
        $guide = User::factory()->create();

        return VoucherOrder::create([
            'voucher_code' => 'CAG-V-TEST22',
            'guiding_id' => 0,
            'guide_id' => $guide->id,
            'tour_title_snapshot' => 'Snapshot Tour',
            'number_of_guests' => 2,
            'price_total' => 380,
            'currency' => 'EUR',
            'recipient_names' => 'Max',
            'buyer_first_name' => 'Bea',
            'buyer_last_name' => 'Buyer',
            'buyer_email' => 'buyer@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'design_type' => $design,
            'pdf_path' => $pdfPath,
            'language' => 'en',
            'voucher_language' => 'en',
            'valid_until' => now()->addYears(3)->endOfYear(),
            'commission_rate' => 0.1,
            'commission_amount' => 38,
        ]);
    }
}
