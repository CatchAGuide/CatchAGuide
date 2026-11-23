<?php

namespace App\Mail\Guide;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** N-34, internal: a guide changed account type or billing data; it needs a new verification. */
class GuideBillingReviewAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public bool $typeChanged = false) {}

    public function build()
    {
        $name = trim($this->user->firstname.' '.$this->user->lastname) ?: $this->user->email;

        return $this->view('mails.guide.billing_review_admin', ['name' => $name, 'info' => $this->user->information])
            ->subject(__('guide_area.account.mail.admin_subject', ['name' => $name]));
    }
}
