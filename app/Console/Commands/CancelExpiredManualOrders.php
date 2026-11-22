<?php

namespace App\Console\Commands;

use App\Actions\CancelOrder;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredManualOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:cancel-expired-manual-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel unpaid manual orders that have exceeded their payment deadline and restore reserved product stock';

    /**
     * Execute the console command.
     */
    public function handle(CancelOrder $cancelOrder): int
    {
        $expiredOrders = Order::query()
            ->where('status', 'pending_payment')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            if ($cancelOrder->handle($order, 'Pesanan dibatalkan otomatis karena melewati batas waktu pembayaran.')) {
                $count++;
            }
        }

        if ($count > 0) {
            Log::info("Cancelled {$count} expired orders and restored product stock.");
            $this->info("Successfully cancelled {$count} expired orders.");
        } else {
            $this->info('No expired orders found.');
        }

        return self::SUCCESS;
    }
}
