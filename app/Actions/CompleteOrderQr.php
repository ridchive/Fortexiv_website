<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Seller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteOrderQr
{
    public function completeClass(string $token, Seller $seller, User $actor): OrderFulfillment
    {
        $token = trim($token);
        $orderId = OrderFulfillment::query()
            ->where(function ($q) use ($token) {
                $q->where('pickup_code', $token)
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_code', $token));
            })
            ->whereBelongsTo($seller)
            ->value('order_id');

        if (! $orderId) {
            $this->invalidQr('pickup_code');
        }

        return DB::transaction(function () use ($token, $seller, $actor, $orderId): OrderFulfillment {
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->firstOrFail();
            $fulfillment = OrderFulfillment::query()
                ->where(function ($q) use ($token) {
                    $q->where('pickup_code', $token)
                        ->orWhereHas('order', fn ($oq) => $oq->where('order_code', $token));
                })
                ->whereBelongsTo($seller)
                ->lockForUpdate()
                ->first();

            if (! $fulfillment) {
                $this->invalidQr('pickup_code');
            }

            if (OrderItem::query()
                ->where('order_fulfillment_id', $fulfillment->id)
                ->where('seller_id', '!=', $seller->id)
                ->exists()) {
                $this->invalidQr('pickup_code');
            }

            $items = OrderItem::query()
                ->where('order_fulfillment_id', $fulfillment->id)
                ->where('seller_id', $seller->id)
                ->orderBy('id')
                ->get(['id']);
            $units = OrderItemUnit::query()
                ->whereIn('order_item_id', $items->modelKeys())
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            if ($units->isEmpty()) {
                throw ValidationException::withMessages(['pickup_code' => 'QR kelas ini sudah pernah digunakan.']);
            }

            $this->ensureReady($order, 'pickup_code');
            $now = now();
            foreach ($units as $unit) {
                $unit->update([
                    'status' => 'delivered',
                    'delivered_at' => $now,
                    'delivered_by' => $actor->id,
                ]);
            }

            $this->synchronizeStatuses($order, $fulfillment, $actor);

            return $fulfillment->fresh();
        }, attempts: 5);
    }

    public function completeProduct(string $token, Seller $seller, User $actor): OrderItemUnit
    {
        $initialUnit = OrderItemUnit::query()
            ->where('qr_token', $token)
            ->with('orderItem')
            ->first();

        if (! $initialUnit || $initialUnit->orderItem->seller_id !== $seller->id) {
            $this->invalidQr();
        }

        return DB::transaction(function () use ($token, $seller, $actor, $initialUnit): OrderItemUnit {
            $order = Order::query()->whereKey($initialUnit->orderItem->order_id)->lockForUpdate()->firstOrFail();
            $unit = OrderItemUnit::query()
                ->where('qr_token', $token)
                ->lockForUpdate()
                ->first();

            if (! $unit) {
                $this->invalidQr();
            }

            $item = OrderItem::query()->whereKey($unit->order_item_id)->lockForUpdate()->firstOrFail();
            if ($item->seller_id !== $seller->id) {
                $this->invalidQr();
            }

            if ($unit->status === 'delivered') {
                throw ValidationException::withMessages(['token' => 'QR produk ini sudah pernah digunakan.']);
            }

            $this->ensureReady($order);
            $unit->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => $actor->id,
            ]);

            $fulfillment = OrderFulfillment::query()
                ->whereKey($item->order_fulfillment_id)
                ->lockForUpdate()
                ->firstOrFail();
            $this->synchronizeStatuses($order, $fulfillment, $actor);

            return $unit->fresh();
        }, attempts: 5);
    }

    private function ensureReady(Order $order, string $errorKey = 'token'): void
    {
        if (! in_array($order->status, ['paid', 'partially_picked_up'], true)
            || ! Ticket::query()->where('order_id', $order->id)->exists()) {
            throw ValidationException::withMessages([$errorKey => 'Pesanan belum dibayar atau belum siap untuk diambil.']);
        }
    }

    private function synchronizeStatuses(Order $order, OrderFulfillment $fulfillment, User $actor): void
    {
        $itemIds = OrderItem::query()
            ->where('order_fulfillment_id', $fulfillment->id)
            ->pluck('id');
        $classUnits = OrderItemUnit::query()->whereIn('order_item_id', $itemIds);
        $classUnitCount = (clone $classUnits)->count();
        $classDeliveredCount = (clone $classUnits)->where('status', 'delivered')->count();
        $classStatus = $classDeliveredCount === $classUnitCount
            ? 'delivered'
            : ($classDeliveredCount > 0 ? 'partially_delivered' : 'pending');

        $fulfillment->update([
            'status' => $classStatus,
            'handed_over_at' => $classStatus === 'delivered' ? ($fulfillment->handed_over_at ?? now()) : null,
            'handed_over_by' => $classStatus === 'delivered' ? $actor->id : null,
        ]);

        $orderItemIds = OrderItem::query()->where('order_id', $order->id)->pluck('id');
        $orderUnits = OrderItemUnit::query()->whereIn('order_item_id', $orderItemIds);
        $orderUnitCount = (clone $orderUnits)->count();
        $orderDeliveredCount = (clone $orderUnits)->where('status', 'delivered')->count();

        if ($orderUnitCount > 0 && $orderDeliveredCount === $orderUnitCount) {
            $order->update(['status' => 'picked_up']);
            $ticket = Ticket::query()->where('order_id', $order->id)->lockForUpdate()->firstOrFail();
            $ticket->update([
                'picked_up_at' => $ticket->picked_up_at ?? now(),
                'picked_up_by' => $actor->id,
            ]);
        } elseif ($orderDeliveredCount > 0) {
            $order->update(['status' => 'partially_picked_up']);
        }
    }

    private function invalidQr(string $errorKey = 'token'): never
    {
        throw ValidationException::withMessages([$errorKey => 'QR tidak valid atau bukan tanggung jawab seller ini.']);
    }
}
