<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('projects', 'has_article')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('has_article')->default(false)->after('is_published');
            });
        }

        // Tandai has_article = true hanya untuk kelas yang artikelnya sudah aktif dan dipublikasikan
        DB::table('projects')->where('is_published', true)->update(['has_article' => true]);
        DB::table('projects')->where('is_published', false)->update(['has_article' => false]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'has_article')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('has_article');
            });
        }
    }
};
