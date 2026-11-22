<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_label', 40)->nullable()->unique();
            $table->json('sections');
            $table->string('status', 20)->index();
            $table->text('summary');
            $table->json('changed_sections');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('terms_versions')->insert([
            'version_label' => 'prototype-1.0',
            'sections' => json_encode([
                [
                    'key' => 'pre-order-payment',
                    'title' => 'Pre-order dan pembayaran',
                    'content' => '<p>Pesanan hanya dapat dibuat selama jendela PO aktif. Buyer bertanggung jawab mengikuti instruksi pembayaran pada detail pesanan dan mengunggah bukti transfer yang benar. Pesanan berstatus menunggu sampai pembayaran diverifikasi admin.</p>',
                ],
                [
                    'key' => 'pickup',
                    'title' => 'Pengambilan pesanan',
                    'content' => '<p>FORTEXIV tidak menyediakan pengiriman. Buyer mengambil produk di acara sesuai tanggal dan petunjuk yang ditampilkan pada detail pesanan. Item dari kelas yang berbeda dapat diserahkan pada waktu terpisah, dan setiap grup kelas memiliki status pengambilan sendiri.</p>',
                ],
                [
                    'key' => 'product-information-stock',
                    'title' => 'Informasi produk dan stok',
                    'content' => '<p>Ketersediaan dan jumlah pesanan diperiksa kembali saat checkout. Jika pembayaran ditolak admin, pesanan dibatalkan dan stok yang dipesan dikembalikan ke ketersediaan.</p>',
                ],
                [
                    'key' => 'prototype-notes',
                    'title' => 'Catatan prototipe',
                    'content' => '<p>Versi ini merupakan prototipe operasional. Ketentuan rinci mengenai pembatalan, pengembalian dana, no-show, dan penyelesaian kendala harus ditetapkan penyelenggara sebelum transaksi produksi dijalankan.</p>',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'status' => 'published',
            'summary' => 'Konten ToS awal dipindahkan dari halaman publik.',
            'changed_sections' => json_encode([
                ['section' => 'Pre-order dan pembayaran', 'change' => 'dipindahkan'],
                ['section' => 'Pengambilan pesanan', 'change' => 'dipindahkan'],
                ['section' => 'Informasi produk dan stok', 'change' => 'dipindahkan'],
                ['section' => 'Catatan prototipe', 'change' => 'dipindahkan'],
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('terms_version_id')->nullable()->constrained('terms_versions')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('terms_version_id');
        });

        Schema::dropIfExists('terms_versions');
    }
};
