<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class PurchaseWindow
{
    public function opensAt(): ?Carbon
    {
        if (filled(config('marketplace.po_start'))) {
            return Carbon::parse(config('marketplace.po_start'));
        }

        if (app()->environment('local', 'testing')) {
            return now()->startOfDay()->subDays(7)->setTime(8, 0);
        }

        return null;
    }

    public function closesAt(): ?Carbon
    {
        if (filled(config('marketplace.po_end'))) {
            return Carbon::parse(config('marketplace.po_end'));
        }

        if (app()->environment('local', 'testing')) {
            return now()->startOfDay()->addDays(14)->setTime(23, 59);
        }

        return null;
    }

    public function eventAt(): ?Carbon
    {
        if (filled(config('marketplace.event_at'))) {
            return Carbon::parse(config('marketplace.event_at'));
        }

        if (app()->environment('local', 'testing')) {
            return now()->startOfDay()->addDays(25)->setTime(9, 0);
        }

        return null;
    }

    public function paymentDeadline(): ?Carbon
    {
        if (filled(config('marketplace.payment_deadline'))) {
            return Carbon::parse(config('marketplace.payment_deadline'));
        }

        if (app()->environment('local', 'testing')) {
            return now()->startOfDay()->addDays(16)->setTime(23, 59);
        }

        return null;
    }

    public function status(): string
    {
        $opensAt = $this->opensAt();
        $closesAt = $this->closesAt();

        if (! $opensAt || ! $closesAt) {
            return 'not_configured';
        }

        if (now()->lt($opensAt)) {
            return 'upcoming';
        }

        if (now()->gt($closesAt)) {
            return 'closed';
        }

        return 'open';
    }

    public function canAcceptOrders(): bool
    {
        return $this->status() === 'open';
    }

    /**
     * @return array{
     *     status: string,
     *     opensAt: ?Carbon,
     *     closesAt: ?Carbon,
     *     eventAt: ?Carbon,
     *     paymentDeadline: ?Carbon,
     *     location: ?string,
     *     pickupInstructions: ?string,
     *     banner: ?string
     * }
     */
    public function details(): array
    {
        return [
            'status' => $this->status(),
            'opensAt' => $this->opensAt(),
            'closesAt' => $this->closesAt(),
            'eventAt' => $this->eventAt(),
            'paymentDeadline' => $this->paymentDeadline(),
            'location' => config('marketplace.event_location')
                ?: (app()->environment('local', 'testing') ? 'Aula Utama SAI, Jl. Cemara Hijau No. 7, Bandung (lokasi contoh)' : null),
            'pickupInstructions' => config('marketplace.pickup_instructions')
                ?: (app()->environment('local', 'testing')
                    ? 'Bawa QR pesanan dari halaman Pesanan Saya. Setiap kelas punya meja sendiri, jadi satu pesanan lintas kelas punya lebih dari satu QR. Tunjukkan QR kepada petugas kelas, cek isi pesanan, lalu petugas mengonfirmasi penyerahan.'
                    : null),
            'banner' => config('marketplace.banner'),
        ];
    }
}
