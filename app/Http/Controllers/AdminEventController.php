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
            'po_end.after' => 'Waktu penutupan PO harus setelah waktu pembukaan PO.',
            'payment_deadline.after_or_equal' => 'Batas waktu pembayaran harus sama dengan atau setelah penutupan PO.',
            'event_at.after_or_equal' => 'Tanggal acara (Hari H) harus sama dengan atau setelah penutupan PO.',
            'change_reason.required' => 'Alasan perubahan jadwal wajib diisi sebagai catatan audit operasional.',
        ]);

        $validated['updated_by'] = $request->user()->id;

        EventSetting::create($validated);
        $purchaseWindow->resetSetting();

        return redirect()->route('admin.event.edit')
            ->with('status', 'Jadwal PO dan pengaturan acara berhasil diperbarui.');
    }
}
