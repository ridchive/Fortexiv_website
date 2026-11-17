<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_settings', function (Blueprint $table): void {
            $table->string('impact_title')->nullable()->after('banner');
            $table->text('impact_intro')->nullable()->after('impact_title');
            $table->longText('impact_body')->nullable()->after('impact_intro');
            $table->string('impact_image')->nullable()->after('impact_body');
            $table->decimal('impact_baseline_co2', 8, 2)->default(20.00)->after('impact_image');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('co2_reduction', 8, 2)->default(0.50)->after('stock');
        });

        $defaultMarkdown = <<<MARKDOWN
## Mengapa Green Ecosystem Impact Tracker?

Proyek FORTEXIV bukan sekadar marketplace produk sekolah biasa. Melalui tema **Green Ecosystem**, empat kelas berkolaborasi menjalankan aksi nyata untuk merawat bumi melalui kebun sekolah, pengolahan limbah daur ulang, dan produksi bernilai guna tinggi.

> "Setiap pilihan konsumsi yang kita buat memiliki jejak terhadap lingkungan. Di FORTEXIV, kami memilih konsumsi bijak demi bumi yang tetap lestari."

### Tiga Pilar Reduksi Emisi Kami:
- **Pemanfaatan Bahan Daur Ulang**: Mengolah limbah kertas, karton, dan kain perca menjadi kartu ucapan, tas belanja, dan tempat pensil tanpa menambang bahan baku baru.
- **Pangan & Pertanian Organik**: Sayuran dan produk olahan dipanen langsung dari kebun kelas tanpa pestisida kimia sintetis dan bebas rantai pasok panjang.
- **Nol Sampah Ekspedisi (Direct Pickup)**: Seluruh pesanan diambil langsung di acara tanpa kemasan bubble wrap, plastik kurir berlebih, maupun emisi bahan bakar kendaraan ekspedisi.

Ketika Anda memesan karya kelas di FORTEXIV, Anda secara langsung berpartisipasi menurunkan jejak karbon sekolah kita.
MARKDOWN;

        // Initialize default GEIT content in latest event setting if exists
        $latestSetting = DB::table('event_settings')->latest('id')->first();
        if ($latestSetting) {
            DB::table('event_settings')
                ->where('id', $latestSetting->id)
                ->update([
                    'impact_title' => 'Jejak Dampak Nyata Green Ecosystem: Konsumsi Bijak, Bumi Lestari',
                    'impact_intro' => 'Dokumentasi transparansi pengurangan jejak emisi karbon melalui proyek nyata dari kebun, dapur, dan studio karya sekolah.',
                    'impact_body' => $defaultMarkdown,
                    'impact_baseline_co2' => 20.00,
                    'updated_at' => now(),
                ]);
        }

        // Assign differentiated initial CO2 reduction factors to existing products
        $products = DB::table('products')->get();
        foreach ($products as $index => $prod) {
            $factors = [0.30, 0.50, 0.80, 1.20, 1.50, 0.40, 0.60, 0.90];
            $factor = $factors[$index % count($factors)];
            DB::table('products')->where('id', $prod->id)->update([
                'co2_reduction' => $factor,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('co2_reduction');
        });

        Schema::table('event_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'impact_title',
                'impact_intro',
                'impact_body',
                'impact_image',
                'impact_baseline_co2',
            ]);
        });
    }
};
