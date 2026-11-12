<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCode extends Notification
{
    public function __construct(public readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode verifikasi email FORTEXIV')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Gunakan kode berikut untuk memverifikasi alamat email Anda:')
            ->line($this->code)
            ->line('Kode ini berlaku selama 10 menit dan hanya dapat digunakan sekali.');
    }
}
