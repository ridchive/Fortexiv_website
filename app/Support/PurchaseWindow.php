<?php

namespace App\Support;

use App\Models\EventSetting;
use Illuminate\Support\Carbon;
use Throwable;

class PurchaseWindow
{
    protected ?EventSetting $currentSetting = null;

    protected bool $settingLoaded = false;

    public function currentSetting(): ?EventSetting
    {
        if (! $this->settingLoaded) {
            try {
                $this->currentSetting = EventSetting::current();
            } catch (Throwable) {
                $this->currentSetting = null;
            }
            $this->settingLoaded = true;
        }

        return $this->currentSetting;
    }

    public function resetSetting(): void
    {
        $this->currentSetting = null;
        $this->settingLoaded = false;
    }

    public function opensAt(): ?Carbon
    {
        $setting = $this->currentSetting();
        if ($setting && $setting->po_start) {
            return Carbon::parse($setting->po_start);
        }

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
        $setting = $this->currentSetting();
        if ($setting && $setting->po_end) {
            return Carbon::parse($setting->po_end);
        }

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
        $setting = $this->currentSetting();
        if ($setting && $setting->event_at) {
            return Carbon::parse($setting->event_at);
        }

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
        $setting = $this->currentSetting();
        if ($setting && $setting->payment_deadline) {
            return Carbon::parse($setting->payment_deadline);
        }

        if (filled(config('marketplace.payment_deadline'))) {
            return Carbon::parse(config('marketplace.payment_deadline'));
        }

        return null;
    }

    public function location(): ?string
    {
        $setting = $this->currentSetting();
        if ($setting && filled($setting->event_location)) {
            return $setting->event_location;
        }

        return config('marketplace.event_location')
            ?: (app()->environment('local', 'testing') ? 'Aula Utama SAI, Jl. Cemara Hijau No. 7, Bandung (lokasi contoh)' : null);
    }

    public function pickupInstructions(): ?string
    {
        $setting = $this->currentSetting();
        if ($setting && filled($setting->pickup_instructions)) {
            return $setting->pickup_instructions;
        }

        return config('marketplace.pickup_instructions')
            ?: (app()->environment('local', 'testing')
                ? 'Bawa QR pesanan dari halaman Pesanan Saya. Setiap kelas punya meja sendiri, jadi satu pesanan lintas kelas punya lebih dari satu QR. Tunjukkan QR kepada petugas kelas, cek isi pesanan, lalu petugas mengonfirmasi penyerahan.'
                : null);
    }

    public function banner(): ?string
    {
        $setting = $this->currentSetting();
        if ($setting && filled($setting->banner)) {
            return $setting->banner;
        }

        return config('marketplace.banner');
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
            'location' => $this->location(),
            'pickupInstructions' => $this->pickupInstructions(),
            'banner' => $this->banner(),
        ];
    }
}
