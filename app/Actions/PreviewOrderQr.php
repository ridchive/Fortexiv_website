<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Seller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PreviewOrderQr
{
    /**
     * @return array{
     *     type: string,
     *     token: string,
     *     order_code: string,
     *     class_name: string,
     *     status: string,
     *     pending_count: int,
     *     products: array<int, array{name: string, quantity: int, delivered_count: int, pending_count: int}>
     * }
     */
    public function handle(string $token, string $type, Seller $seller): array
    {
        $token = Str::upper(trim($token));

        // Auto-detect or normalize token type
        if (Str::startsWith($token, 'FTXP-')) {
            $type = 'product';
        } elseif (Str::startsWith($token, 'FTXS-')) {
            $type = 'store';
        } elseif (Str::startsWith($token, 'FTXC-') || Str::startsWith($token, 'FTXG-') || Str::startsWith($token, 'FX-') || Str::startsWith($token, 'FXT-')) {
            $type = 'class';
        }

        if ($type === 'store') {
            return $this->previewStore($token, $seller);
        }

        if ($type === 'class') {
            $fulfillment = OrderFulfillment::query()
                ->where(function ($q) use ($token) {
                    $q->where('pickup_code', $token)
                        ->orWhereHas('order', function ($oq) use ($token) {
                            $oq->where('order_code', $token)
                                ->orWhereHas('ticket', fn ($tq) => $tq->where('ticket_code', $token));
                        });
                })
                ->whereBelongsTo($seller)
                ->with(['order.ticket', 'order.buyer', 'project', 'seller', 'items.units'])
                ->first();

            // Fallback: If not found as class, check if it's a product unit token
            if (! $fulfillment) {
                $unit = OrderItemUnit::query()
                    ->where('qr_token', $token)
                    ->with(['orderItem.fulfillment.project', 'orderItem.fulfillment.seller', 'orderItem.order.buyer'])
                    ->first();

                if ($unit && $unit->orderItem->seller_id === $seller->id) {
                    return $this->previewUnit($unit);
                }

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
                throw ValidationException::withMessages(['token' => 'Seluruh produk untuk kelas ini pada pesanan tersebut sudah pernah diserahkan (Delivered).']);
            }

            $this->ensureReady($fulfillment->order->status, $fulfillment->order->ticket !== null);

            return [
                'type' => 'class',
                'token' => $fulfillment->pickup_code,
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

        // Fallback: If not found as unit, check if it's a class token
        if (! $unit) {
            $fulfillment = OrderFulfillment::query()
                ->where(function ($q) use ($token) {
                    $q->where('pickup_code', $token)
                        ->orWhereHas('order', function ($oq) use ($token) {
                            $oq->where('order_code', $token)
                                ->orWhereHas('ticket', fn ($tq) => $tq->where('ticket_code', $token));
                        });
                })
                ->whereBelongsTo($seller)
                ->with(['order.ticket', 'order.buyer', 'project', 'seller', 'items.units'])
                ->first();

            if ($fulfillment) {
                return $this->handle($token, 'class', $seller);
            }

            $this->invalidQr();
        }

        if ($unit->orderItem->seller_id !== $seller->id) {
            $this->invalidQr();
        }

        return $this->previewUnit($unit);
    }

    private function previewStore(string $token, Seller $seller): array
    {
        $parts = explode('-', $token);
        if (count($parts) < 4) {
            $this->invalidQr();
        }
        $buyerId = (int) $parts[1];
        $sellerId = (int) $parts[2];
        $hash = $parts[3];
        $expectedHash = Str::upper(substr(hash_hmac('sha256', "buyer:{$buyerId}:seller:{$sellerId}", config('app.key')), 0, 12));
        if (! hash_equals($expectedHash, $hash)) {
            $this->invalidQr();
        }
        if ($sellerId !== $seller->id) {
            throw ValidationException::withMessages(['token' => 'QR ini ditujukan untuk toko lain, bukan toko Anda.']);
        }
        $buyer = User::query()->find($buyerId);
        if (! $buyer) {
            $this->invalidQr();
        }

        $items = OrderItem::query()
            ->where('seller_id', $seller->id)
            ->whereHas('order', function ($q) use ($buyerId) {
                $q->where('buyer_id', $buyerId)
                    ->whereIn('status', ['paid', 'partially_picked_up']);
            })
            ->with(['units', 'order'])
            ->get();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['token' => 'Tidak ada pesanan aktif siap ambil untuk pembeli ini di toko Anda.']);
        }

        $grouped = [];
        foreach ($items as $item) {
            $name = $item->product_name;
            if (! isset($grouped[$name])) {
                $grouped[$name] = [
                    'name' => $name,
                    'quantity' => 0,
                    'delivered_count' => 0,
                    'pending_count' => 0,
                ];
            }
            $grouped[$name]['quantity'] += (int) $item->quantity;
            $grouped[$name]['delivered_count'] += $item->units->where('status', 'delivered')->count();
            $grouped[$name]['pending_count'] += $item->units->where('status', 'pending')->count();
        }

        $products = array_values($grouped);
        $pendingCount = array_sum(array_column($products, 'pending_count'));

        if ($pendingCount === 0) {
            throw ValidationException::withMessages(['token' => 'Seluruh produk untuk pembeli ini di toko Anda sudah pernah diserahkan (Delivered).']);
        }

        return [
            'type' => 'store',
            'token' => $token,
            'order_code' => 'Semua Pesanan Aktif',
            'buyer_name' => $buyer->name,
            'buyer_phone' => $buyer->phone ?? '—',
            'pickup_contact_name' => $buyer->name,
            'class_name' => $seller->project?->class_name ?? $seller->store_name,
            'status' => 'pending',
            'pending_count' => $pendingCount,
            'products' => $products,
        ];
    }

    private function previewUnit(OrderItemUnit $unit): array
    {
        if ($unit->status === 'delivered') {
            throw ValidationException::withMessages(['token' => 'QR produk ini sudah pernah digunakan.']);
        }

        $order = $unit->orderItem->order;
        $this->ensureReady($order->status, $order->ticket()->exists());

        $fulfillment = $unit->orderItem->fulfillment;

        return [
            'type' => 'product',
            'token' => $unit->qr_token,
            'order_code' => $order->order_code,
            'product_name' => $unit->orderItem->product_name,
            'buyer_name' => $order->pickup_contact_name ?? $order->buyer?->name ?? 'Pembeli',
            'buyer_phone' => $order->pickup_contact_phone ?? $order->buyer?->phone ?? '—',
            'pickup_contact_name' => $order->pickup_contact_name ?? $order->buyer?->name ?? '—',
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
