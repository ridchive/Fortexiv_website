<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Seller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteOrderFulfillment
{
    public function handle(string $pickupCode, Seller $seller, User $actor): OrderFulfillment
    {
        return DB::transaction(function () use ($pickupCode, $seller, $actor): OrderFulfillment {
            $fulfillment = OrderFulfillment::query()
                ->where('pickup_code', strtoupper(trim($pickupCode)))
                ->whereBelongsTo($seller)
                ->lockForUpdate()
                ->first();

            if (! $fulfillment) {
                throw ValidationException::withMessages(['pickup_code' => 'Kode pickup tidak valid untuk kelas ini.']);
            }

            $order = Order::query()->whereKey($fulfillment->order_id)->lockForUpdate()->firstOrFail();
            $ticket = Ticket::query()->where('order_id', $order->id)->lockForUpdate()->first();

            if (! $ticket || ! in_array($order->status, ['paid', 'partially_picked_up'], true)) {
                throw ValidationException::withMessages(['pickup_code' => 'Pesanan belum dibayar atau tidak siap untuk diambil.']);
            }

            if ($fulfillment->status !== 'pending') {
                throw ValidationException::withMessages(['pickup_code' => 'Grup kelas ini sudah diserahkan.']);
            }

            $fulfillment->update([
                'status' => 'handed_over',
                'handed_over_at' => now(),
                'handed_over_by' => $actor->id,
            ]);

            $hasPendingGroups = OrderFulfillment::query()
                ->where('order_id', $order->id)
                ->where('status', 'pending')
                ->exists();

            $order->update(['status' => $hasPendingGroups ? 'partially_picked_up' : 'picked_up']);

            if (! $hasPendingGroups) {
                $ticket->update([
                    'picked_up_at' => now(),
                    'picked_up_by' => $actor->id,
                ]);
            }

            return $fulfillment->load(['order.buyer', 'seller']);
        });
    }
}
