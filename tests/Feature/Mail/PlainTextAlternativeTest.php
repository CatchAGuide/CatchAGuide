<?php

namespace Tests\Feature\Mail;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

/**
 * Every mailable renders an HTML-only Blade view; the MessageSending listener must add a
 * text/plain part so messages go out as multipart/alternative (an HTML-only body is a spam signal).
 */
class PlainTextAlternativeTest extends TestCase
{
    use DatabaseTransactions;

    private function sentEmail(Mailable $mailable): Email
    {
        Mail::mailer('array')->to('guest@example.com')->send($mailable);

        return Mail::mailer('array')->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    }

    public function test_html_only_mail_gets_a_text_part(): void
    {
        $email = $this->sentEmail((new Mailable())->subject('Test')->html('<p>Hallo <a href="https://catchaguide.de/b/1">Buchung ansehen</a></p>'));

        $this->assertSame('Hallo Buchung ansehen (https://catchaguide.de/b/1)', $email->getTextBody());
        $this->assertStringContainsString('<p>Hallo', $email->getHtmlBody());
        $this->assertStringContainsString('multipart/alternative', $email->toString());
    }

    public function test_existing_text_part_is_left_alone(): void
    {
        $mailable = new class extends Mailable {
            public function build()
            {
                return $this->subject('Test')->html('<p>HTML</p>')->text('mails.plain-text-alternative-test');
            }
        };
        $this->app['view']->getFinder()->addLocation(__DIR__ . '/stubs');

        $this->assertSame('Eigener Text', trim($this->sentEmail($mailable)->getTextBody()));
    }
}
