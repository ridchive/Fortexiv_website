<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use UnexpectedValueException;

#[Signature('marketplace:sync-midtrans-payments')]
#[Description('Check pending Midtrans orders against the Midtrans status API and apply their current status')]
class SyncMidtransPayments extends Command
{
    /**
     * Checks the least recently checked pending orders first, so orders whose Snap
     * transaction was never paid do not block newer orders from being checked.
     */
    public function handle(MidtransService $midtrans): int
    {
        if (! filled(config('services.midtrans.server_key'))) {
            $this->info('Midtrans is not configured; nothing to check.');

            return self::SUCCESS;
        }

        $orders = Order::query()
            ->select('orders.*')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'pending_payment')
            ->where('payments.gateway', Payment::GATEWAY_MIDTRANS)
            ->where('payments.status', 'pending')
            ->whereNotNull('payments.snap_redirect_url')
            ->orderBy('payments.updated_at')
            ->orderBy('orders.id')
            ->limit(max(1, (int) config('services.midtrans.status_check_batch_size')))
            ->with('payment')
            ->get();

        foreach ($orders as $order) {
            try {
                $status = $midtrans->syncTransactionStatus($order);
                $this->line("{$order->order_code}: {$status}");
            } catch (ConnectionException|RequestException|UnexpectedValueException|HttpException $exception) {
                Log::info('Scheduled Midtrans status check skipped an order.', [
                    'order_code' => $order->order_code,
                    'exception' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);
                $this->line("{$order->order_code}: skipped");
            }

            $order->payment?->touch();
        }

        $this->info("Checked {$orders->count()} pending Midtrans order(s).");

        return self::SUCCESS;
    }
}
