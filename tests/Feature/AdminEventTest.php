<?php

namespace Tests\Feature;

use App\Models\EventSetting;
use App\Models\User;
use App\Support\PurchaseWindow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_event_settings(): void
    {
        $this->get(route('admin.event.edit'))
            ->assertRedirect(route('login'));

        $buyer = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($buyer)
            ->get(route('admin.event.edit'))
            ->assertForbidden();

        $sellerUser = User::factory()->create(['role' => 'seller']);
        $sellerUser->seller()->create(['store_name' => 'Toko Uji']);
        $this->actingAs($sellerUser)
            ->get(route('admin.event.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_view_event_settings_page_and_navigation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.event.edit'));

        $response->assertOk()
            ->assertSee('Kelola Jadwal PO &amp; Acara', false)
            ->assertSee('Waktu Pembukaan PO')
            ->assertSee('Waktu Penutupan PO')
            ->assertSee('Alasan Perubahan');

        // Test dashboard displays the PO schedule section and navigation link
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('STATUS JENDELA PRE-ORDER (PO)')
            ->assertSee('Kelola Jadwal PO')
            ->assertSee(route('admin.event.edit'), false);
    }

    public function test_admin_can_update_event_settings_with_audit_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']);
        $purchaseWindow = app(PurchaseWindow::class);

        $poStart = now()->subDay()->setTime(8, 0);
        $poEnd = now()->addDays(5)->setTime(23, 59);
        $eventAt = now()->addDays(10)->setTime(9, 0);

        $response = $this->actingAs($admin)->put(route('admin.event.update'), [
            'po_start' => $poStart->format('Y-m-d H:i:s'),
            'po_end' => $poEnd->format('Y-m-d H:i:s'),
            'payment_deadline' => $poEnd->copy()->addDay()->format('Y-m-d H:i:s'),
            'event_at' => $eventAt->format('Y-m-d H:i:s'),
            'event_location' => 'Gedung Serbaguna Sekolah',
            'pickup_instructions' => 'Tunjukkan QR di meja stan masing-masing kelas.',
            'banner' => 'Pre-order dibuka untuk semua karya siswa.',
            'change_reason' => 'Perpanjangan jadwal PO 5 hari ke depan.',
        ]);

        $response->assertRedirect(route('admin.event.edit'))
            ->assertSessionHas('status', 'Jadwal PO dan pengaturan acara berhasil diperbarui.');

        $this->assertDatabaseHas('event_settings', [
            'event_location' => 'Gedung Serbaguna Sekolah',
            'change_reason' => 'Perpanjangan jadwal PO 5 hari ke depan.',
            'updated_by' => $admin->id,
        ]);

        $purchaseWindow->resetSetting();
        $this->assertSame('open', $purchaseWindow->status());
        $this->assertTrue($purchaseWindow->canAcceptOrders());
        $this->assertSame('Gedung Serbaguna Sekolah', $purchaseWindow->location());
    }

    public function test_closing_po_window_dynamically_stops_accepting_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $purchaseWindow = app(PurchaseWindow::class);

        // Close PO window by setting po_end in the past
        $this->actingAs($admin)->put(route('admin.event.update'), [
            'po_start' => now()->subDays(5)->format('Y-m-d H:i:s'),
            'po_end' => now()->subHour()->format('Y-m-d H:i:s'),
            'payment_deadline' => now()->format('Y-m-d H:i:s'),
            'event_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'event_location' => 'Aula Sekolah',
            'pickup_instructions' => 'Ambil di meja.',
            'banner' => 'PO ditutup.',
            'change_reason' => 'Penutupan resmi jendela PO.',
        ])->assertRedirect();

        $purchaseWindow->resetSetting();
        $this->assertSame('closed', $purchaseWindow->status());
        $this->assertFalse($purchaseWindow->canAcceptOrders());
    }

    public function test_validation_rejects_invalid_date_order_and_missing_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // po_end before po_start, missing change_reason
        $this->actingAs($admin)->put(route('admin.event.update'), [
            'po_start' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'po_end' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'event_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'event_location' => 'Aula Sekolah',
            'change_reason' => '',
        ])->assertSessionHasErrors(['po_end', 'change_reason']);
    }

    public function test_admin_can_save_settings_with_empty_payment_deadline(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $purchaseWindow = app(PurchaseWindow::class);

        $poStart = now()->subHour();
        $poEnd = now()->addDays(7);
        $eventAt = now()->addDays(14);

        $response = $this->actingAs($admin)->put(route('admin.event.update'), [
            'po_start' => $poStart->format('Y-m-d H:i:s'),
            'po_end' => $poEnd->format('Y-m-d H:i:s'),
            'payment_deadline' => '',
            'event_at' => $eventAt->format('Y-m-d H:i:s'),
            'event_location' => 'Aula Utama',
            'change_reason' => 'Batas pembayaran tidak ditentukan (opsional dikosongkan).',
        ]);

        $response->assertRedirect(route('admin.event.edit'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_settings', [
            'payment_deadline' => null,
            'event_location' => 'Aula Utama',
        ]);

        $purchaseWindow->resetSetting();
        $this->assertNull($purchaseWindow->paymentDeadline());
    }

    public function test_validation_rejects_payment_deadline_before_po_end(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $poStart = now();
        $poEnd = now()->addDays(5);
        $invalidPaymentDeadline = now()->addDays(2); // before po_end!

        $response = $this->actingAs($admin)->put(route('admin.event.update'), [
            'po_start' => $poStart->format('Y-m-d H:i:s'),
            'po_end' => $poEnd->format('Y-m-d H:i:s'),
            'payment_deadline' => $invalidPaymentDeadline->format('Y-m-d H:i:s'),
            'event_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'event_location' => 'Aula',
            'change_reason' => 'Uji batas waktu salah.',
        ]);

        $response->assertSessionHasErrors([
            'payment_deadline' => 'Jika diisi, Batas Waktu Pembayaran harus sama dengan atau setelah Waktu Penutupan PO.',
        ]);
    }

    public function test_updating_event_settings_preserves_hero_and_impact_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        EventSetting::create([
            'po_start' => now()->subDay(),
            'po_end' => now()->addDays(3),
            'event_at' => now()->addDays(5),
            'event_location' => 'Lokasi Lama',
            'hero_title' => 'Judul Hero Kustom',
            'hero_description' => 'Deskripsi Hero Kustom',
            'hero_image' => 'hero/test.jpg',
            'impact_title' => 'Judul Dampak Kustom',
            'impact_intro' => 'Intro Kustom',
            'impact_body' => 'Body Kustom',
            'impact_image' => 'impact/test.jpg',
            'impact_baseline_co2' => 25.50,
            'change_reason' => 'Inisialisasi',
        ]);

        $this->actingAs($admin)->put(route('admin.event.update'), [
            'po_start' => now()->format('Y-m-d H:i:s'),
            'po_end' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'event_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'event_location' => 'Lokasi Baru',
            'change_reason' => 'Ubah jadwal PO tanpa merusak Hero.',
        ])->assertRedirect(route('admin.event.edit'));

        $latest = EventSetting::current();
        $this->assertNotNull($latest);
        $this->assertSame('Judul Hero Kustom', $latest->hero_title);
        $this->assertSame('Deskripsi Hero Kustom', $latest->hero_description);
        $this->assertSame('hero/test.jpg', $latest->hero_image);
        $this->assertSame('Judul Dampak Kustom', $latest->impact_title);
        $this->assertSame('Intro Kustom', $latest->impact_intro);
        $this->assertSame('Body Kustom', $latest->impact_body);
        $this->assertSame('impact/test.jpg', $latest->impact_image);
        $this->assertEquals(25.50, $latest->impact_baseline_co2);
    }
}
