<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_settings', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('po_start')->nullable();
            $table->dateTime('po_end')->nullable();
            $table->dateTime('payment_deadline')->nullable();
            $table->dateTime('event_at')->nullable();
            $table->string('event_location')->nullable();
            $table->text('pickup_instructions')->nullable();
            $table->string('banner')->nullable();
            $table->text('change_reason')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('event_settings')->insert([
            'po_start' => config('marketplace.po_start'),
            'po_end' => config('marketplace.po_end'),
            'payment_deadline' => config('marketplace.payment_deadline'),
            'event_at' => config('marketplace.event_at') ?: now()->startOfDay()->addDays(25)->setTime(9, 0)->toDateTimeString(),
            'event_location' => config('marketplace.event_location') ?: 'Aula Utama SAI, Jl. Cemara Hijau No. 7, Bandung',
            'pickup_instructions' => config('marketplace.pickup_instructions') ?: 'Bawa QR pesanan dari halaman Pesanan Saya. Setiap kelas punya meja sendiri, jadi satu pesanan lintas kelas punya lebih dari satu QR. Tunjukkan QR kepada petugas kelas, cek isi pesanan, lalu petugas mengonfirmasi penyerahan.',
            'banner' => config('marketplace.banner') ?: 'Satu periode PO untuk semua kelas. Semua pesanan diambil langsung di acara, tidak ada pengiriman.',
            'change_reason' => 'Inisialisasi konfigurasi jadwal acara dan jendela PO.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('event_settings');
    }
};
