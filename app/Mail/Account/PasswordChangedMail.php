<?php

namespace App\Mail\Account;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** N-36: security notice after a password change on the account page. */
class PasswordChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function build()
    {
        return $this->view('mails.account.password_changed', [
            'changedAt' => ($this->user->password_changed_at ?? now())->timezone(config('app.timezone'))->format('d.m.Y H:i'),
        ])->subject(__('guide_area.account.mail.password_subject'));
    }
}
