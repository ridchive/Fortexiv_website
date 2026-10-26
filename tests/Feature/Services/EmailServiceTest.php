<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    public function test_it_sends_a_mailable_to_multiple_recipients(): void
    {
        Mail::fake();

        $mailable = new class extends Mailable
        {
            public function envelope(): Envelope
            {
                return new Envelope(subject: 'Service email test');
            }

            public function content(): Content
            {
                return new Content(htmlString: '<p>Reusable email service</p>');
            }
        };

        app(EmailService::class)->send($mailable, [
            'first@example.test',
            'second@example.test',
        ]);

        Mail::assertSent($mailable::class, fn (Mailable $sent): bool => $sent->hasTo('first@example.test') && $sent->hasTo('second@example.test'));
    }

    public function test_it_sends_email_verification_notifications(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->make();

        app(EmailService::class)->sendEmailVerificationLink($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
