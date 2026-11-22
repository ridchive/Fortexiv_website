<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[FORTEXIV] Pembayaran Terverifikasi - Pesanan #{$this->order->order_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-verified',
        );
    }
}
