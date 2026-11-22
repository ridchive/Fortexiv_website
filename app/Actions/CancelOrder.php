<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CancelOrder
{
    /**
     * Cancel an order safely and restore reserved product stock.
     */
    public function handle(Order $order, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($order, $reason): bool {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'pending_payment') {
                return false;
            }

            // Restore product stock for each ordered item
            $lockedOrder->loadMissing('items');
            foreach ($lockedOrder->items as $item) {
                if ($item->product_id) {
                    Product::query()
                        ->whereKey($item->product_id)
                        ->lockForUpdate()
                        ->increment('stock', $item->quantity);
                }
            }

            // Update payment record if exists
            $payment = $lockedOrder->payment()->lockForUpdate()->first();
            if ($payment) {
                $notes = $payment->notes;
                if (filled($reason)) {
                    $notes = filled($notes) ? ($notes.' | '.$reason) : $reason;
                }
                $payment->update([
                    'status' => 'cancelled',
                    'notes' => $notes ? Str::limit($notes, 255, '') : null,
                ]);
            }

            $lockedOrder->update(['status' => 'cancelled']);

            return true;
        });
    }
}
