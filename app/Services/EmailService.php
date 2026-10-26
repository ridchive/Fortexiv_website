<?php

namespace App\Services;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Password;

class EmailService
{
    public function __construct(private MailFactory $mailFactory) {}

    /**
     * @param  string|array<int, string>  $recipients
     */
    public function send(Mailable $mailable, string|array $recipients): void
    {
        $this->mailFactory->mailer()->to($recipients)->send($mailable);
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function sendEmailVerificationLink(MustVerifyEmail $user): void
    {
        $user->sendEmailVerificationNotification();
    }
}
