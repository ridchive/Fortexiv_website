<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use UnexpectedValueException;

class MidtransService
{
    /**
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransaction(Order $order): array
    {
        $serverKey = config('services.midtrans.server_key');
        $baseUrl = config('services.midtrans.base_url');

        $order->loadMissing(['buyer', 'items']);

        $customerDetails = ['first_name' => $order->buyer->name, 'email' => $order->buyer->email];

        if (filled($order->buyer->phone)) {
            $customerDetails['phone'] = $order->buyer->phone;
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(15)
            ->post($baseUrl.'/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $order->order_code,
                    'gross_amount' => $order->total_price,
                ],
                'item_details' => $order->items->map(fn ($item) => [
                    'id' => (string) $item->product_id,
                    'price' => $item->price_at_order,
                    'quantity' => $item->quantity,
                    'name' => Str::limit($item->product_name, 50, ''),
                ])->all(),
                'customer_details' => $customerDetails,
                'callbacks' => [
                    'finish' => route('orders.show', $order),
                ],
            ]);

        $transaction = $response->throw()->json();

        if (! is_array($transaction)
            || ! is_string($transaction['token'] ?? null)
            || ! filled($transaction['token'])
            || ! is_string($transaction['redirect_url'] ?? null)
            || ! $this->isValidRedirectUrl($transaction['redirect_url'], $transaction['token'])) {
            throw new UnexpectedValueException('Midtrans returned an invalid Snap transaction response.');
        }

        return [
            'token' => $transaction['token'],
            'redirect_url' => $transaction['redirect_url'],
        ];
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    public function hasValidSignature(array $notification): bool
    {
        $serverKey = config('services.midtrans.server_key');
        $signature = $notification['signature_key'] ?? null;

        if (! filled($serverKey)
            || ! is_string($signature)
            || ! is_string($notification['order_id'] ?? null)
            || ! is_string($notification['status_code'] ?? null)
            || ! is_string($notification['gross_amount'] ?? null)) {
            return false;
        }

        $expectedSignature = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].$serverKey,
        );

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransactionStatus(string $orderId): array
    {
        $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(10)
            ->get(config('services.midtrans.status_url').'/v2/'.rawurlencode($orderId).'/status');

        $transaction = $response->throw()->json();

        if (! is_array($transaction)
            || ($transaction['order_id'] ?? null) !== $orderId
            || ! is_string($transaction['gross_amount'] ?? null)
            || ! is_string($transaction['transaction_status'] ?? null)) {
            throw new UnexpectedValueException('Midtrans returned an invalid transaction status response.');
        }

        return $transaction;
    }

    /**
     * @param  array<string, mixed>  $notification
     * @param  array<string, mixed>  $transaction
     */
    public function handleNotification(array $notification, array $transaction): void
    {
        DB::transaction(function () use ($notification, $transaction): void {
            $order = Order::query()
                ->with('items')
                ->where('order_code', $notification['order_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($payment->gateway === Payment::GATEWAY_MIDTRANS, 404);
            abort_unless($this->amountMatchesOrder($notification['gross_amount'], $order), 422, 'Payment amount does not match the order.');
            abort_unless($this->amountMatchesOrder($transaction['gross_amount'], $order), 422, 'Midtrans transaction amount does not match the order.');

            $paymentDetails = [];

            if (is_string($transaction['payment_type'] ?? null)) {
                $paymentDetails['method'] = $transaction['payment_type'];
            }

            if (is_string($transaction['transaction_id'] ?? null)) {
                $paymentDetails['transaction_id'] = $transaction['transaction_id'];
            }

            $transactionStatus = Str::lower($transaction['transaction_status']);
            $isPaid = $transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && ($transaction['fraud_status'] ?? null) === 'accept');

            if ($isPaid) {
                if ($order->status !== 'pending_payment' && $order->status !== 'cancelled') {
                    return;
                }

                $payment->update($paymentDetails + [
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);
                $order->update(['status' => 'paid']);
                $order->ticket()->firstOrCreate([], [
                    'ticket_code' => 'FXT-'.Str::upper(Str::random(12)),
                    'issued_at' => now(),
                ]);

                return;
            }

            if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'failure', 'refund', 'chargeback'], true)) {
                if (in_array($order->status, ['paid', 'picked_up'], true)) {
                    $ticket = Ticket::query()->where('order_id', $order->id)->lockForUpdate()->first();

                    if ($ticket?->picked_up_at) {
                        $payment->update($paymentDetails + [
                            'status' => 'failed',
                            'notes' => 'Midtrans reported a payment reversal after pickup; administrator follow-up is required.',
                        ]);

                        return;
                    }

                    $ticket?->delete();
                } elseif ($order->status !== 'pending_payment') {
                    return;
                }

                $payment->update($paymentDetails + ['status' => 'failed']);
                $order->update(['status' => 'cancelled']);
                $this->returnReservedStock($order);

                return;
            }

            if ($transactionStatus === 'pending' && $order->status === 'pending_payment') {
                $payment->update($paymentDetails);
            }
        });
    }

    private function isValidRedirectUrl(string $redirectUrl, string $token): bool
    {
        $expectedHost = parse_url(config('services.midtrans.base_url'), PHP_URL_HOST);
        $parts = parse_url($redirectUrl);
        $path = (string) ($parts['path'] ?? '');

        return filter_var($redirectUrl, FILTER_VALIDATE_URL) !== false
            && is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && ($parts['host'] ?? null) === $expectedHost
            && ! isset($parts['port'])
            && ! isset($parts['user'])
            && ! isset($parts['pass'])
            && ! isset($parts['fragment'])
            && Str::startsWith($path, ['/snap/v2/vtweb/', '/snap/v4/redirection/'])
            && Str::endsWith($path, '/'.$token);
    }

    private function amountMatchesOrder(string $grossAmount, Order $order): bool
    {
        if (preg_match('/^\d+(?:\.\d{1,2})?$/D', $grossAmount) !== 1) {
            return false;
        }

        [$whole, $fraction] = array_pad(explode('.', $grossAmount, 2), 2, '');

        if (strlen($whole) > 10) {
            return false;
        }

        $amountInCents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return $amountInCents === $order->total_price * 100;
    }

    private function returnReservedStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product_id === null) {
                continue;
            }

            Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
        }
    }
}
