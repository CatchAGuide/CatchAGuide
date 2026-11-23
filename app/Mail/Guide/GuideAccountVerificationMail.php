<?php

namespace App\Mail\Guide;

use App\Enums\AccountVerificationStatus;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** N-35: result of the billing verification (approved, or rejected with the reason). */
class GuideAccountVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function build()
    {
        $approved = $this->user->account_verification_status === AccountVerificationStatus::Verified;

        return $this->view('mails.guide.account_verification', ['approved' => $approved])
            ->subject(__($approved ? 'guide_area.account.mail.approved_subject' : 'guide_area.account.mail.rejected_subject'));
    }
}
