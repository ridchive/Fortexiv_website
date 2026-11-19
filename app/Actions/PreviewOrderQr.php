<?php

namespace App\Actions;

use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Seller;
use Illuminate\Validation\ValidationException;

class PreviewOrderQr
{
    /**
     * @return array{
     *     type: string,
     *     order_code: string,
     *     class_name: string,
     *     status: string,
     *     pending_count: int,
     *     products: array<int, array{name: string, quantity: int, delivered_count: int, pending_count: int}>
     * }
     */
    public function handle(string $token, string $type, Seller $seller): array
    {
        if ($type === 'class') {
            $fulfillment = OrderFulfillment::query()
                ->where('pickup_code', $token)
                ->whereBelongsTo($seller)
                ->with(['order', 'project', 'seller', 'items.units'])
                ->first();

            if (! $fulfillment) {
                $this->invalidQr();
            }

            if (OrderItem::query()
                ->where('order_fulfillment_id', $fulfillment->id)
                ->where('seller_id', '!=', $seller->id)
                ->exists()) {
                $this->invalidQr();
            }

            $products = $fulfillment->items->map(function (OrderItem $item): array {
                $deliveredCount = $item->units->where('status', 'delivered')->count();
                $pendingCount = $item->units->where('status', 'pending')->count();

                return [
                    'name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'delivered_count' => $deliveredCount,
                    'pending_count' => $pendingCount,
                ];
            })->all();
            $pendingCount = array_sum(array_column($products, 'pending_count'));

            if ($pendingCount === 0) {
                throw ValidationException::withMessages(['token' => 'QR kelas ini sudah pernah digunakan.']);
            }

            $this->ensureReady($fulfillment->order->status, $fulfillment->order->ticket()->exists());

            return [
                'type' => 'class',
                'order_code' => $fulfillment->order->order_code,
                'buyer_name' => $fulfillment->order->pickup_contact_name ?? $fulfillment->order->buyer?->name ?? 'Pembeli',
                'buyer_phone' => $fulfillment->order->pickup_contact_phone ?? $fulfillment->order->buyer?->phone ?? '—',
                'pickup_contact_name' => $fulfillment->order->pickup_contact_name ?? $fulfillment->order->buyer?->name ?? '—',
                'class_name' => $fulfillment->class_name ?? $fulfillment->project?->class_name ?? $fulfillment->seller->store_name,
                'status' => $fulfillment->status,
                'pending_count' => $pendingCount,
                'products' => $products,
            ];
        }

        $unit = OrderItemUnit::query()
            ->where('qr_token', $token)
            ->with(['orderItem.fulfillment.project', 'orderItem.fulfillment.seller', 'orderItem.order.buyer'])
            ->first();

        if (! $unit || $unit->orderItem->seller_id !== $seller->id) {
            $this->invalidQr();
        }

        if ($unit->status === 'delivered') {
            throw ValidationException::withMessages(['token' => 'QR produk ini sudah pernah digunakan.']);
        }

        $this->ensureReady($unit->orderItem->order->status, $unit->orderItem->order->ticket()->exists());

        $fulfillment = $unit->orderItem->fulfillment;

        return [
            'type' => 'product',
            'order_code' => $unit->orderItem->order->order_code,
            'buyer_name' => $unit->orderItem->order->pickup_contact_name ?? $unit->orderItem->order->buyer?->name ?? 'Pembeli',
            'buyer_phone' => $unit->orderItem->order->pickup_contact_phone ?? $unit->orderItem->order->buyer?->phone ?? '—',
            'pickup_contact_name' => $unit->orderItem->order->pickup_contact_name ?? $unit->orderItem->order->buyer?->name ?? '—',
            'class_name' => $fulfillment?->class_name ?? $fulfillment?->project?->class_name ?? $fulfillment?->seller?->store_name ?? 'Kelas',
            'status' => $unit->status,
            'pending_count' => 1,
            'products' => [[
                'name' => $unit->orderItem->product_name,
                'quantity' => 1,
                'delivered_count' => 0,
                'pending_count' => 1,
            ]],
        ];
    }

    private function ensureReady(string $orderStatus, bool $hasTicket): void
    {
        if (! $hasTicket || ! in_array($orderStatus, ['paid', 'partially_picked_up'], true)) {
            throw ValidationException::withMessages(['token' => 'Pesanan belum dibayar atau belum siap untuk diambil.']);
        }
    }

    private function invalidQr(): never
    {
        throw ValidationException::withMessages(['token' => 'QR tidak valid atau bukan tanggung jawab seller ini.']);
    }
}
