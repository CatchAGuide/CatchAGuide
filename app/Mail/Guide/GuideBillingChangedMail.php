<?php

namespace App\Mail\Guide;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** N-33: security notice to the guide after a change of account type or billing data. */
class GuideBillingChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public bool $typeChanged = false) {}

    public function build()
    {
        return $this->view('mails.guide.billing_changed')
            ->subject(__('guide_area.account.mail.changed_subject'));
    }
}
