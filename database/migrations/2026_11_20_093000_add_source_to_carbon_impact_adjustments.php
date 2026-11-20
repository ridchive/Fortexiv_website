<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carbon_impact_adjustments', function (Blueprint $table): void {
            $table->string('source', 30)->default('admin')->after('performed_by');
        });
    }

    public function down(): void
    {
        Schema::table('carbon_impact_adjustments', function (Blueprint $table): void {
            $table->dropColumn('source');
        });
    }
};
