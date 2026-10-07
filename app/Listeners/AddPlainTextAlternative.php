<?php

namespace App\Listeners;

use App\Services\Email\HtmlToPlainText;
use Illuminate\Mail\Events\MessageSending;

/**
 * Gives every outgoing HTML-only email a text/plain part, so it goes out as
 * multipart/alternative instead of the HTML-only shape spam filters score against.
 */
class AddPlainTextAlternative
{
    public function __construct(private HtmlToPlainText $converter)
    {
    }

    public function handle(MessageSending $event): void
    {
        $message = $event->message;
        $html = $message->getHtmlBody();

        if ($message->getTextBody() !== null || ! is_string($html) || trim($html) === '') {
            return;
        }

        $text = $this->converter->convert($html);
        if ($text !== '') {
            $message->text($text, $message->getHtmlCharset() ?? 'utf-8');
        }
    }
}
