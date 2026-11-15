<?php

namespace App\Http\Controllers;

use App\Models\EventSetting;
use App\Models\Order;
use App\Support\PurchaseWindow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEventController extends Controller
{
    public function edit(PurchaseWindow $purchaseWindow): View
    {
        $currentSetting = EventSetting::current();
        $history = EventSetting::query()
            ->with('updater')
            ->latest('id')
            ->paginate(10);

        $activeOrdersCount = Order::query()
            ->whereNotIn('status', ['cancelled'])
            ->count();

        return view('admin.event.form', [
            'setting' => $currentSetting,
            'history' => $history,
            'window' => $purchaseWindow->details(),
            'activeOrdersCount' => $activeOrdersCount,
        ]);
    }

    public function update(Request $request, PurchaseWindow $purchaseWindow): RedirectResponse
    {
        if (! filled($request->input('payment_deadline'))) {
            $request->merge(['payment_deadline' => null]);
        }

        $validated = $request->validate([
            'po_start' => ['required', 'date'],
            'po_end' => ['required', 'date', 'after:po_start'],
            'payment_deadline' => ['nullable', 'date', 'after_or_equal:po_end'],
            'event_at' => ['required', 'date', 'after_or_equal:po_end'],
            'event_location' => ['required', 'string', 'max:255'],
            'pickup_instructions' => ['nullable', 'string', 'max:2000'],
            'banner' => ['nullable', 'string', 'max:255'],
            'change_reason' => ['required', 'string', 'max:500'],
        ], [
            'po_start.required' => 'Waktu pembukaan PO wajib diisi.',
            'po_end.required' => 'Waktu penutupan PO wajib diisi.',
            'po_end.after' => 'Waktu penutupan PO harus setelah waktu pembukaan PO.',
            'payment_deadline.after_or_equal' => 'Jika diisi, Batas Waktu Pembayaran harus sama dengan atau setelah Waktu Penutupan PO.',
            'event_at.required' => 'Tanggal acara (Hari H) wajib diisi.',
            'event_at.after_or_equal' => 'Tanggal acara (Hari H) harus sama dengan atau setelah Waktu Penutupan PO.',
            'event_location.required' => 'Lokasi acara / pengambilan wajib diisi.',
            'change_reason.required' => 'Alasan perubahan jadwal wajib diisi sebagai catatan audit operasional.',
        ]);

        $validated['payment_deadline'] = filled($validated['payment_deadline'] ?? null) ? $validated['payment_deadline'] : null;
        $validated['updated_by'] = $request->user()->id;

        EventSetting::create($validated);
        $purchaseWindow->resetSetting();

        return redirect()->route('admin.event.edit')
            ->with('status', 'Jadwal PO dan pengaturan acara berhasil diperbarui.');
    }
}
