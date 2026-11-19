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
            $table->string('hero_title')->nullable()->after('banner');
            $table->text('hero_description')->nullable()->after('hero_title');
            $table->string('hero_image')->nullable()->after('hero_description');
        });

        $latestSetting = DB::table('event_settings')->latest('id')->first();
        if ($latestSetting) {
            DB::table('event_settings')
                ->where('id', $latestSetting->id)
                ->update([
                    'hero_title' => 'Konsumsi Bijak, Bumi Lestari.',
                    'hero_description' => 'Empat kelas mengerjakan proyek nyata dari kebun, dapur, dan studio sekolah. Baca ceritanya, lalu pesan produknya lewat satu periode PO terpadu.',
                    'hero_image' => $latestSetting->impact_image ?? null,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('event_settings', function (Blueprint $table): void {
            $table->dropColumn(['hero_title', 'hero_description', 'hero_image']);
        });
    }
};
