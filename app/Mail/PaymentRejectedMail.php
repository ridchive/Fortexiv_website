<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[FORTEXIV] Bukti Pembayaran Pesanan #{$this->order->order_code} Memerlukan Perbaikan",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-rejected',
        );
    }
}
